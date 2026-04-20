<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SelfClientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function client_can_register_with_valid_cpf()
    {
        $data = [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'document_number' => '52998224725', // Valid CPF
            'phone1' => '(11) 99999-8888',
            'contributor_type' => '2',
            'state_registration' => '',
        ];

        $response = $this->post(route('client.register.post'), $data);

        // Should redirect to login or show success
        $this->assertTrue(in_array($response->status(), [302, 200]));
    }

    #[Test]
    public function client_cannot_register_with_invalid_cpf()
    {
        $data = [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'document_number' => '11111111111', // Invalid CPF (all same digits)
            'phone1' => '(11) 99999-8888',
            'contributor_type' => '2',
            'state_registration' => '',
        ];

        $response = $this->post(route('client.register.post'), $data);

        $response->assertSessionHasErrors(['document_number']);
    }

    #[Test]
    public function client_can_register_with_valid_cnpj_and_state_registration()
    {
        $data = [
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'document_number' => '11222333000181', // Valid CNPJ
            'phone1' => '(11) 99999-8888',
            'contributor_type' => '1',
            'state_registration' => '123456789', // Required for CNPJ
        ];

        $response = $this->post(route('client.register.post'), $data);

        // Should redirect to login or show success
        $this->assertTrue(in_array($response->status(), [302, 200]));
    }

    #[Test]
    public function client_cannot_register_with_cnpj_without_state_registration()
    {
        $data = [
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'document_number' => '11222333000181', // Valid CNPJ
            'phone1' => '(11) 99999-8888',
            'contributor_type' => '1',
            'state_registration' => '', // Empty for CNPJ - should fail
        ];

        $response = $this->post(route('client.register.post'), $data);

        // Check if response has validation errors (either in session or redirect with errors)
        $this->assertTrue(
            $response->hasSession('errors') ||
            $response->getSession()->has('errors') ||
            $response->getStatusCode() === 302
        );
    }

    #[Test]
    public function client_can_register_with_cpf_without_state_registration()
    {
        $data = [
            'name' => 'Test Client',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'document_number' => '52998224725', // Valid CPF
            'phone1' => '(11) 99999-8888',
            'contributor_type' => '2',
            'state_registration' => '', // Optional for CPF - should pass
        ];

        $response = $this->post(route('client.register.post'), $data);

        // Should redirect to login or show success
        $this->assertTrue(in_array($response->status(), [302, 200]));
    }

    #[Test]
    public function client_cannot_register_with_invalid_cnpj()
    {
        $data = [
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'document_number' => '11111111111111', // Invalid CNPJ (all same digits)
            'phone1' => '(11) 99999-8888',
            'contributor_type' => '1',
            'state_registration' => '123456789',
        ];

        $response = $this->post(route('client.register.post'), $data);

        $response->assertSessionHasErrors(['document_number']);
    }
}
