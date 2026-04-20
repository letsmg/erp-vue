<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_required_fields()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_casts_is_active_to_boolean()
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    public function test_user_access_level_defaults_to_zero()
    {
        $user = User::factory()->create([
            'access_level' => null,
        ]);

        $this->assertEquals(0, $user->access_level);
    }

    public function test_user_is_admin_returns_true_for_admin()
    {
        $user = User::factory()->create([
            'access_level' => 1,
        ]);

        $this->assertTrue($user->isAdmin());
    }

    public function test_user_is_admin_returns_false_for_staff()
    {
        $user = User::factory()->create([
            'access_level' => 0,
        ]);

        $this->assertFalse($user->isAdmin());
    }

    public function test_user_is_staff_returns_true_for_admin()
    {
        $user = User::factory()->create([
            'access_level' => 1,
        ]);

        $this->assertTrue($user->isStaff());
    }

    public function test_user_is_staff_returns_true_for_staff()
    {
        $user = User::factory()->create([
            'access_level' => 0,
        ]);

        $this->assertTrue($user->isStaff());
    }

    public function test_user_is_staff_returns_false_for_client()
    {
        $user = User::factory()->create([
            'access_level' => 2,
        ]);

        $this->assertFalse($user->isStaff());
    }

    public function test_user_email_must_be_unique()
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create([
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_password_is_hashed()
    {
        $plainPassword = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($plainPassword),
        ]);

        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($plainPassword, $user->password));
    }
}
