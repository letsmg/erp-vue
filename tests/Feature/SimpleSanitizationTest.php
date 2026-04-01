<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleSanitizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function middleware_sanitizes_input_data()
    {
        // Testa diretamente o helper
        $data = [
            'name' => '<b>Test</b> User <script>alert("xss")</script>',
            'description' => '<p>Description with <b>HTML</b></p>',
            'meta_title' => '<script>alert("xss")</script>Meta Title',
            'schema_markup' => '<script type="application/ld+json">{"@context": "https://schema.org"}</script>',
            'google_tag_manager' => '<!-- Google Tag Manager --><script>dataLayer = [];</script>',
        ];

        // Aplica sanitização com exceções
        $sanitized = \App\Helpers\SanitizerHelper::sanitize($data, ['schema_markup', 'google_tag_manager']);

        // Verifica se os campos foram sanitizados
        $this->assertEquals('Test User', $sanitized['name']);
        $this->assertEquals('Description with HTML', $sanitized['description']);
        $this->assertEquals('Meta Title', $sanitized['meta_title']);
        
        // Verifica se os campos HTML foram preservados
        $this->assertEquals('<script type="application/ld+json">{"@context": "https://schema.org"}</script>', $sanitized['schema_markup']);
        $this->assertEquals('<!-- Google Tag Manager --><script>dataLayer = [];</script>', $sanitized['google_tag_manager']);
    }

    /** @test */
    public function seo_sanitization_works()
    {
        $seoData = [
            'meta_title' => '<script>alert("xss")</script>Meta Title',
            'meta_description' => '<p>Description with <b>HTML</b></p>',
            'schema_markup' => '<script type="application/ld+json">{"@context": "https://schema.org"}</script>',
            'google_tag_manager' => '<!-- Google Tag Manager --><script>dataLayer = [];</script>',
            'h1' => '<h1>Heading</h1>',
            'text1' => '<em>Text</em> content',
        ];

        $sanitized = \App\Helpers\SanitizerHelper::sanitizeSeoData($seoData);

        // Verifica se os campos foram sanitizados
        $this->assertEquals('Meta Title', $sanitized['meta_title']);
        $this->assertEquals('Description with HTML', $sanitized['meta_description']);
        $this->assertEquals('Heading', $sanitized['h1']);
        $this->assertEquals('Text content', $sanitized['text1']);
        
        // Verifica se os campos HTML foram preservados
        $this->assertEquals('<script type="application/ld+json">{"@context": "https://schema.org"}</script>', $sanitized['schema_markup']);
        $this->assertEquals('<!-- Google Tag Manager --><script>dataLayer = [];</script>', $sanitized['google_tag_manager']);
    }

    /** @test */
    public function xss_payloads_are_removed()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            'javascript:alert("XSS")',
            '<img src="x" onerror="alert(\'XSS\')">',
            '<svg onload="alert(\'XSS\')">',
            '"><script>alert("XSS")</script>',
        ];

        foreach ($xssPayloads as $payload) {
            $data = ['content' => $payload . ' Safe content'];
            $sanitized = \App\Helpers\SanitizerHelper::sanitize($data);
            
            // Verifica se o XSS foi removido
            $this->assertStringNotContainsString('<script>', $sanitized['content']);
            $this->assertStringNotContainsString('javascript:', $sanitized['content']);
            $this->assertStringNotContainsString('onerror', $sanitized['content']);
            $this->assertStringNotContainsString('onload', $sanitized['content']);
            
            // Verifica se o conteúdo seguro permanece
            $this->assertStringContainsString('Safe content', $sanitized['content']);
        }
    }

    /** @test */
    public function supplier_creation_is_sanitized()
    {
        $user = User::factory()->create();

        $data = [
            'company_name' => '<b>Test</b> Company <script>alert("xss")</script> LTDA',
            'cnpj' => '12.345.678/0001-90',
            'state_registration' => '123456789',
            'email' => 'test@company.com',
            'address' => '<p>Rua de Teste, 123</p>',
            'neighborhood' => '  Centro  ',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01001-000',
            'contact_name_1' => '<i>João</i> Silva',
            'phone_1' => '(11) 99999-9999',
            'is_active' => true,
        ];

        $response = $this->actingAs($user)->post(route('suppliers.store'), $data);

        $response->assertRedirect();
        
        $supplier = \App\Models\Supplier::where('cnpj', '12.345.678/0001-90')->first();
        $this->assertNotNull($supplier);
        
        // Verifica se os campos foram sanitizados
        $this->assertEquals('Test Company LTDA', $supplier->company_name);
        $this->assertEquals('Rua de Teste, 123', $supplier->address);
        $this->assertEquals('Centro', $supplier->neighborhood);
        $this->assertEquals('João Silva', $supplier->contact_name_1);
    }
}
