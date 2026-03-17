<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create(['access_level' => 1]);
    }

    /** 1. TESTES DE ACESSO (PROTEÇÃO) **/

    public function test_usuario_nao_logado_nao_pode_ver_fornecedores()
    {
        $response = $this->get(route('suppliers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_logado_pode_ver_lista_de_fornecedores()
    {
        $user = User::factory()->create(); // Usuário comum

        $response = $this->actingAs($user)->get(route('suppliers.index'));

        $response->assertStatus(200);
    }

    /** 2. TESTE DE CRIAÇÃO (STORE) **/

    public function test_usuario_pode_cadastrar_fornecedor()
    {
        $user = User::factory()->create();

        $dados = [
            'company_name' => 'Fornecedor de Teste LTDA',
            'cnpj' => '12.345.678/0001-90',
            'state_registration' => '123456789',
            'email' => 'contato@fornecedor.com',
            'address' => 'Rua de Teste, 123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01001-000',
            'contact_name_1' => 'João Silva',
            'phone_1' => '(11) 99999-9999',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->post(route('suppliers.store'), $dados);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['cnpj' => '12.345.678/0001-90']);
    }

    /** 3. TESTE DE EDIÇÃO (UPDATE) **/

    public function test_usuario_pode_atualizar_fornecedor()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create(['company_name' => 'Antigo Nome']);

        $response = $this->actingAs($user)->put(route('suppliers.update', $supplier), [
            'company_name' => 'Novo Nome Atualizado',
            'cnpj' => $supplier->cnpj, // CNPJ é unique, mantemos o mesmo
            'state_registration' => $supplier->state_registration,
            'email' => 'novo@email.com',
            'address' => $supplier->address,
            'neighborhood' => $supplier->neighborhood,
            'city' => $supplier->city,
            'state' => $supplier->state,
            'zip_code' => $supplier->zip_code,
            'contact_name_1' => $supplier->contact_name_1,
            'phone_1' => $supplier->phone_1,
            'is_active' => false, // Desativando no update
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_name' => 'Novo Nome Atualizado',
            'is_active' => 0 // SQLite interpreta boolean como 0/1
        ]);
    }

    /** 4. TESTE DE EXCLUSÃO (DELETE) **/

    public function test_usuario_pode_excluir_fornecedor()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($user)->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}