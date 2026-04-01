<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\User;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private ClientRepository $repository;
    private ClientService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ClientRepository();
        $this->service = new ClientService($this->repository);
    }

    /** @test */
    public function it_can_create_client_with_user()
    {
        $clientData = [
            'name' => 'Test Client',
            'document_type' => 'CNPJ',
            'document_number' => '12345678901234',
            'state_registration' => '123456789',
            'municipal_registration' => '987654321',
            'contributor_type' => 1,
            'phone1' => '(11) 99999-9999',
            'contact1' => 'Test Contact',
            'is_active' => true,
        ];

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $result = $this->service->createClientWithUser($clientData, $userData);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client',
            'document_number' => '12345678901234',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'access_level' => 2, // CLIENT
        ]);

        $this->assertEquals('Test Client', $result['client']->name);
        $this->assertEquals('test@example.com', $result['user']->email);
    }

    /** @test */
    public function it_can_create_client_without_user()
    {
        $data = [
            'name' => 'Test Client Only',
            'document_type' => 'CPF',
            'document_number' => '12345678901',
            'contributor_type' => 9, // Não Contribuinte
            'is_active' => true,
        ];

        $client = $this->service->createClientOnly($data);

        $this->assertDatabaseHas('clients', [
            'name' => 'Test Client Only',
            'document_number' => '12345678901',
            'contributor_type' => 9,
        ]);

        $this->assertEquals('Test Client Only', $client->name);
        $this->assertNull($client->user_id);
    }

    /** @test */
    public function it_validates_cpf_correctly()
    {
        $validCPFs = [
            '12345678901',
            '11144477735',
            '12345678909',
        ];

        foreach ($validCPFs as $cpf) {
            $result = $this->service->validateDocument($cpf);
            $this->assertTrue($result['valid'], "CPF {$cpf} should be valid");
            $this->assertEquals('CPF', $result['type']);
        }
    }

    /** @test */
    public function it_validates_cnpj_correctly()
    {
        $validCNPJs = [
            '12345678901234',
            '11122233000181',
            '04123456789012',
        ];

        foreach ($validCNPJs as $cnpj) {
            $result = $this->service->validateDocument($cnpj);
            $this->assertTrue($result['valid'], "CNPJ {$cnpj} should be valid");
            $this->assertEquals('CNPJ', $result['type']);
        }
    }

    /** @test */
    public function it_rejects_invalid_cpf()
    {
        $invalidCPFs = [
            '123456789012', // 12 dígitos
            '11111111111', // Todos iguais
            '12345678900', // Dígitos verificadores inválidos
        ];

        foreach ($invalidCPFs as $cpf) {
            $result = $this->service->validateDocument($cpf);
            $this->assertFalse($result['valid'], "CPF {$cpf} should be invalid");
        }
    }

    /** @test */
    public function it_rejects_invalid_cnpj()
    {
        $invalidCNPJs = [
            '1234567890123', // 13 dígitos
            '11111111111111', // Todos iguais
            '12345678901235', // Dígitos verificadores inválidos
        ];

        foreach ($invalidCNPJs as $cnpj) {
            $result = $this->service->validateDocument($cnpj);
            $this->assertFalse($result['valid'], "CNPJ {$cnpj} should be invalid");
        }
    }

    /** @test */
    public function it_detects_duplicate_document()
    {
        // Create first client
        Client::factory()->create([
            'document_number' => '12345678901234',
            'document_type' => 'CNPJ',
        ]);

        // Try to create second client with same document
        $result = $this->service->validateDocument('12345678901234');

        $this->assertFalse($result['valid']);
        $this->assertEquals('duplicate', $result['type']);
        $this->assertEquals('Documento já cadastrado', $result['message']);
    }

    /** @test */
    public function it_prepares_client_data_correctly()
    {
        $data = [
            'name' => 'Test Client',
            'document_number' => '12.345.678/9012-34',
            'state_registration' => '123456789',
            'contributor_type' => 1,
        ];

        $prepared = $this->service->prepareClientData($data, 'CNPJ');

        $this->assertEquals('Test Client', $prepared['name']);
        $this->assertEquals('12345678901234', $prepared['document_number']);
        $this->assertEquals('CNPJ', $prepared['document_type']);
        $this->assertEquals('123456789', $prepared['state_registration']);
        $this->assertEquals(1, $prepared['contributor_type']);
    }

    /** @test */
    public function it_prepares_pf_data_correctly()
    {
        $data = [
            'name' => 'Test PF Client',
            'document_number' => '123.456.789-01',
            'contributor_type' => 2,
        ];

        $prepared = $this->service->prepareClientData($data, 'CPF');

        $this->assertEquals('Test PF Client', $prepared['name']);
        $this->assertEquals('12345678901', $prepared['document_number']);
        $this->assertEquals('CPF', $prepared['document_type']);
        $this->assertNull($prepared['state_registration']); // PF não tem IE
        $this->assertNull($prepared['municipal_registration']); // PF não tem IM
        $this->assertEquals(2, $prepared['contributor_type']);
    }

    /** @test */
    public function it_searches_client_by_document()
    {
        $client = Client::factory()->create([
            'document_number' => '12345678901234',
        ]);

        $found = $this->service->searchClient('12345678901234');

        $this->assertNotNull($found);
        $this->assertEquals($client->id, $found->id);
    }

    /** @test */
    public function it_searches_client_by_email()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $client = Client::factory()->create(['user_id' => $user->id]);

        $found = $this->service->searchClient('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($client->id, $found->id);
    }

    /** @test */
    public function it_formats_document_correctly()
    {
        $clientCPF = Client::factory()->create([
            'document_type' => 'CPF',
            'document_number' => '12345678901',
        ]);

        $clientCNPJ = Client::factory()->create([
            'document_type' => 'CNPJ',
            'document_number' => '12345678901234',
        ]);

        $this->assertEquals('123.456.789-01', $clientCPF->formatted_document);
        $this->assertEquals('12.345.678/9012-34', $clientCNPJ->formatted_document);
    }

    /** @test */
    public function it_checks_contributor_type_correctly()
    {
        $clientContributor = Client::factory()->create(['contributor_type' => 1]);
        $clientExempt = Client::factory()->create(['contributor_type' => 2]);
        $clientNonContributor = Client::factory()->create(['contributor_type' => 9]);

        $this->assertTrue($clientContributor->isICMSContributor());
        $this->assertFalse($clientContributor->isICMSExempt());
        $this->assertFalse($clientContributor->isNonContributor());

        $this->assertFalse($clientExempt->isICMSContributor());
        $this->assertTrue($clientExempt->isICMSExempt());
        $this->assertFalse($clientExempt->isNonContributor());

        $this->assertFalse($clientNonContributor->isICMSContributor());
        $this->assertFalse($clientNonContributor->isICMSExempt());
        $this->assertTrue($clientNonContributor->isNonContributor());
    }
}
