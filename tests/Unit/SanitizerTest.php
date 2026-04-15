<?php

namespace Tests\Unit;

use App\Helpers\SanitizerHelper;
use Tests\TestCase;

class SanitizerTest extends TestCase
{
    /** @test */
    public function it_sanitizes_basic_data()
    {
        $data = [
            'name' => '  John Doe  ',
            'description' => '<p>This is <b>bold</b> text</p>',
            'email' => 'john@example.com',
            'number' => 123,
            'boolean' => true,
        ];

        $sanitized = SanitizerHelper::sanitize($data);

        $this->assertEquals('John Doe', $sanitized['name']);
        $this->assertEquals('This is bold text', $sanitized['description']);
        $this->assertEquals('john@example.com', $sanitized['email']);
        $this->assertEquals(123, $sanitized['number']);
        $this->assertTrue($sanitized['boolean']);
    }

    /** @test */
    public function it_preserves_specified_fields_from_sanitization()
    {
        $data = [
            'title' => '<script>alert("xss")</script>Title',
            'schema_markup' => '<script type="application/ld+json">{"@context": "https://schema.org"}</script>',
            'google_tag_manager' => '<!-- Google Tag Manager --><script>...</script>',
            'description' => '<p>Normal description</p>',
        ];

        $sanitized = SanitizerHelper::sanitize($data, ['schema_markup', 'google_tag_manager']);

        $this->assertEquals('Title', $sanitized['title']);
        $this->assertEquals('<script type="application/ld+json">{"@context": "https://schema.org"}</script>', $sanitized['schema_markup']);
        $this->assertEquals('<!-- Google Tag Manager --><script>...</script>', $sanitized['google_tag_manager']);
        $this->assertEquals('Normal description', $sanitized['description']);
    }

    /** @test */
    public function sanitize_seo_data_preserves_html_fields()
    {
        $seoData = [
            'meta_description' => '<p>Description with <b>HTML</b></p>',
            'schema_markup' => '<script type="application/ld+json">{"@context": "https://schema.org"}</script>',
            'google_tag_manager' => '<!-- Google Tag Manager --><script>dataLayer = [];</script>',
        ];

        $sanitized = SanitizerHelper::sanitizeSeoData($seoData);

        $this->assertEquals('Description with HTML', $sanitized['meta_description']);
        $this->assertEquals('<script type="application/ld+json">{"@context": "https://schema.org"}</script>', $sanitized['schema_markup']);
        $this->assertEquals('<!-- Google Tag Manager --><script>dataLayer = [];</script>', $sanitized['google_tag_manager']);
    }

    /** @test */
    public function it_handles_nested_arrays()
    {
        $data = [
            'user' => [
                'name' => '  John  ',
                'bio' => '<p>Bio with <b>formatting</b></p>',
            ],
            'tags' => ['tag1', '  tag2  ', 'tag3'],
        ];

        $sanitized = SanitizerHelper::sanitize($data);

        $this->assertEquals('John', $sanitized['user']['name']);
        $this->assertEquals('Bio with formatting', $sanitized['user']['bio']);
        $this->assertEquals(['tag1', 'tag2', 'tag3'], $sanitized['tags']);
    }

    /** @test */
    public function it_handles_null_and_empty_values()
    {
        $data = [
            'name' => null,
            'description' => '',
            'content' => '  <p>Content</p>  ',
        ];

        $sanitized = SanitizerHelper::sanitize($data);

        $this->assertNull($sanitized['name']);
        $this->assertEquals('', $sanitized['description']);
        $this->assertEquals('Content', $sanitized['content']);
    }

    /** @test */
    public function it_prevents_xss_attacks()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            'javascript:alert("XSS")',
            '<img src="x" onerror="alert(\'XSS\')">',
            '<svg onload="alert(\'XSS\')">',
            '"><script>alert("XSS")</script>',
        ];

        foreach ($xssPayloads as $payload) {
            $data = ['content' => $payload];
            $sanitized = SanitizerHelper::sanitize($data);
            
            $this->assertStringNotContainsString('<script>', $sanitized['content']);
            $this->assertStringNotContainsString('javascript:', $sanitized['content']);
            $this->assertStringNotContainsString('onerror', $sanitized['content']);
            $this->assertStringNotContainsString('onload', $sanitized['content']);
        }
    }
}
