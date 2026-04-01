<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_clients()
    {
        $admin = User::factory()->admin()->create();
        Client::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/api/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'clients' => [
                        'data' => [],
                        'links' => [],
                        'meta' => [],
                    ],
                    'filters',
                ],
            ]);
    }

    /** @test */
    public function operator_can_view_clients()
    {
        $operator = User::factory()->create(['access_level' => 0]); // OPERATOR
        Client::factory()->count(3)->create();

        $response = $this->actingAs($operator)->get('/api/clients');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_cannot_view_clients()
    {
        $client = User::factory()->client()->create();
        Client::factory()->count(3)->create();

        $response = $this->actingAs($client)->get('/api/clients');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_view_clients()
    {
        Client::factory()->count(3)->create();

        $response = $this->get('/api/clients');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_create_client_with_user()
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'name' => 'Test Client',
            'document_type' => 'CNPJ',
            'document_number' => '12345678901234',
            'state_registration' => '123456789',
            'contributor_type' => 1,
            'user_name' => 'Test User',
            'user_email' => 'test@example.com',
            'user_password' => 'password123',
            'user_password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($admin)->post('/api/clients', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'document_number',
                    'document_type',
                    'contributor_type',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'document_number' => '12345678901234',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'access_level' => 2, // CLIENT
        ]);
    }

    /** @test */
    public function admin_can_create_client_without_user()
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'name' => 'Test Client Only',
            'document_type' => 'CPF',
            'document_number' => '12345678901',
            'contributor_type' => 9,
        ];

        $response = $this->actingAs($admin)->post('/api/clients', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client Only',
            'document_number' => '12345678901',
            'user_id' => null,
        ]);
    }

    /** @test */
    public function client_cannot_create_client()
    {
        $client = User::factory()->client()->create();

        $data = [
            'name' => 'Test Client',
            'document_type' => 'CPF',
            'document_number' => '12345678901',
        ];

        $response = $this->actingAs($client)->post('/api/clients', $data);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_validates_unique_document()
    {
        $admin = User::factory()->admin()->create();
        
        // Create first client
        Client::factory()->create([
            'document_number' => '12345678901234',
            'document_type' => 'CNPJ',
        ]);

        $data = [
            'name' => 'Test Client 2',
            'document_type' => 'CNPJ',
            'document_number' => '12345678901234', // Same document
        ];

        $response = $this->actingAs($admin)->post('/api/clients', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document_number']);
    }

    /** @test */
    public function it_validates_cpf_format()
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'name' => 'Test Client',
            'document_type' => 'CPF',
            'document_number' => '123456789012', // 12 digits instead of 11
        ];

        $response = $this->actingAs($admin)->post('/api/clients', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document_number']);
    }

    /** @test */
    public function it_validates_cnpj_format()
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'name' => 'Test Client',
            'document_type' => 'CNPJ',
            'document_number' => '1234567890123', // 13 digits instead of 14
        ];

        $response = $this->actingAs($admin)->post('/api/clients', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['document_number']);
    }

    /** @test */
    public function it_validates_required_state_registration_for_cnpj()
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'name' => 'Test Client',
            'document_type' => 'CNPJ',
            'document_number' => '12345678901234',
            'state_registration' => '', // Empty for CNPJ
        ];

        $response = $this->actingAs($admin)->post('/api/clients', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['state_registration']);
    }

    /** @test */
    public function admin_can_view_client()
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($admin)->get("/api/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'document_number',
                    'document_type',
                    'contributor_type',
                    'is_active',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'addresses' => [],
                ],
            ]);
    }

    /** @test */
    public function client_can_view_own_client()
    {
        $user = User::factory()->client()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/api/clients/{$client->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function client_cannot_view_other_client()
    {
        $user = User::factory()->client()->create();
        $otherClient = Client::factory()->create();

        $response = $this->actingAs($user)->get("/api/clients/{$otherClient->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_client()
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $data = [
            'name' => 'Updated Client Name',
            'phone1' => '(11) 99999-8888',
        ];

        $response = $this->actingAs($admin)->put("/api/clients/{$client->id}", $data);

        $response->assertStatus(200);
        
        $client->refresh();
        $this->assertEquals('Updated Client Name', $client->name);
        $this->assertEquals('(11) 99999-8888', $client->phone1);
    }

    /** @test */
    public function admin_can_toggle_client_status()
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post("/api/clients/{$client->id}/toggle-status");

        $response->assertStatus(200);
        
        $client->refresh();
        $this->assertFalse($client->is_active);
    }

    /** @test */
    public function admin_can_delete_client()
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($admin)->delete("/api/clients/{$client->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    /** @test */
    public function operator_cannot_delete_client()
    {
        $operator = User::factory()->create(['access_level' => 0]); // OPERATOR
        $client = Client::factory()->create();

        $response = $this->actingAs($operator)->delete("/api/clients/{$client->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    /** @test */
    public function it_can_search_client_by_document()
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create([
            'document_number' => '12345678901234',
        ]);

        $response = $this->actingAs($admin)->get('/api/clients/search?search=12345678901234');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'data' => [
                    'id' => $client->id,
                    'name' => $client->name,
                ],
            ]);
    }

    /** @test */
    public function it_can_search_client_by_email()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['email' => 'test@example.com']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get('/api/clients/search?search=test@example.com');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'data' => [
                    'id' => $client->id,
                    'name' => $client->name,
                ],
            ]);
    }

    /** @test */
    public function it_can_validate_document()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/api/clients/validate-document', [
            'document' => '12345678901234',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'type' => 'CNPJ',
                ],
            ]);
    }

    /** @test */
    public function it_rejects_invalid_document_validation()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/api/clients/validate-document', [
            'document' => '11111111111111', // Invalid CPF
        ]);

        $response->assertStatus(400)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'CPF inválido',
            ]);
    }
}
