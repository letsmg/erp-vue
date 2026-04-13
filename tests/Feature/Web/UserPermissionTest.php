<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as InertiaAssert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seeders opcionais se precisar de dados adicionais
        //$this->seed();
    }

    /** --- TESTES DE ACESSO --- **/

    public function test_admin_pode_acessar_dashboard()
    {
        $admin = User::factory()->create([
            'access_level' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_tela_de_login_esta_acessivel()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_usuario_nao_autenticado_e_redirecionado_para_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_pode_fazer_logout()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test'])
            ->post(route('logout'), ['_token' => 'test']);

        $response->assertStatus(302);
        $this->assertGuest();
    }

    /** --- TESTES DE PERMISSÃO --- **/

    public function test_admin_pode_acessar_lista_de_usuarios()
    {
        $admin = User::factory()->create(['access_level' => 1]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertStatus(200)
                 ->assertInertia(fn (InertiaAssert $page) =>
                     $page->component('Users/Index')
                          ->has('users')
                 );
    }

    public function test_usuario_comum_pode_visualizar_usuarios_nivel_0()
    {
        $user = User::factory()->create(['access_level' => 0]);
        $outroUsuario = User::factory()->create(['access_level' => 0]);

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertStatus(200)
            ->assertInertia(fn (InertiaAssert $page) =>
                $page->component('Users/Index')
                    ->has('users', 2)
                    ->where('users', fn ($users) =>
                        collect($users)->pluck('id')->contains($outroUsuario->id)
                    )
            );
    }

    public function test_usuario_comum_nao_pode_resetar_senha_outro_nivel_0()
    {
        $user = User::factory()->create(['access_level' => 0]);
        $outroUsuario = User::factory()->create(['access_level' => 0]);

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test'])
            ->patch(route('users.reset', $outroUsuario), ['_token' => 'test']);

        // ❌ Bloqueado pela policy
        $response->assertStatus(403);
    }

    public function test_admin_pode_cadastrar_usuario()
    {
        $admin = User::factory()->create(['access_level' => 1]);
        $senhaForte = 'Senha@Forte123';

        $novoUsuario = [
            'name' => '<b>Clone</b> <script>alert("xss")</script>',
            'email' => 'clone@teste.com',
            'password' => $senhaForte,
            'password_confirmation' => $senhaForte,
            'access_level' => 0,
            'is_active' => true,
            '_token' => 'test',
        ];

        $response = $this->actingAs($admin)
            ->withSession(['_token' => 'test'])
            ->post(route('users.store'), $novoUsuario);

        if ($response->status() !== 302) {
            dump($response->getContent());
        }

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'clone@teste.com',
            'is_active' => 1
        ]);
        
        // Verifica se a sanitização foi aplicada
        $user = User::where('email', 'clone@teste.com')->first();
        $this->assertEquals('Clone', $user->name);
    }

    public function test_usuario_comum_nao_pode_deletar_ninguem()
    {
        $user = User::factory()->create(['access_level' => 0]);

        // Tenta deletar um usuário admin
        $alvoAdmin = User::factory()->create(['access_level' => 1]);
        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test'])
            ->delete(route('users.destroy', $alvoAdmin), ['_token' => 'test']);

        // Verifica se a resposta é 403 (proibido)
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $alvoAdmin->id]);

        // Tenta deletar outro usuário nível 0
        $alvoNivel0 = User::factory()->create(['access_level' => 0]);
        $response = $this->actingAs($user)
            ->withSession(['_token' => 'test'])
            ->delete(route('users.destroy', $alvoNivel0), ['_token' => 'test']);

        // Verifica se a resposta é 403 (proibido)
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $alvoNivel0->id]);
    }
}
