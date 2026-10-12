<?php

namespace Tests\Unit;

use App\Services\AI\GeminiProvider;
use App\Services\AI\GroqProvider;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AIProviderTest extends TestCase
{
    #[Test]
    public function gemini_provider_is_not_configured_without_api_key()
    {
        config(['services.ai.gemini.api_key' => null]);

        $provider = new GeminiProvider();

        $this->assertFalse($provider->isConfigured());
    }

    #[Test]
    public function gemini_provider_is_configured_with_api_key()
    {
        $provider = new GeminiProvider();
        
        // Use reflection to set the API key
        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('apiKey');
        $property->setAccessible(true);
        $property->setValue($provider, 'test-key');

        $this->assertTrue($provider->isConfigured());
    }

    #[Test]
    public function gemini_provider_returns_correct_names()
    {
        config([
            'services.ai.gemini.api_key' => 'test-key',
            'services.ai.gemini.model' => 'gemini-2.0-flash-exp',
        ]);

        $provider = new GeminiProvider();

        $this->assertEquals('gemini', $provider->getProviderName());
        $this->assertEquals('gemini-2.0-flash-exp', $provider->getModelName());
    }

    #[Test]
    public function gemini_provider_truncates_long_text()
    {
        config([
            'services.ai.gemini.api_key' => 'test-key',
            'services.ai.gemini.model' => 'gemini-2.0-flash-exp',
            'services.ai.gemini.max_chars' => 100,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Generated notes']]]]
                ]
            ], 200)
        ]);

        $provider = new GeminiProvider();
        $longText = str_repeat('a', 500);

        $result = $provider->generateNotes($longText, 'Test Material');

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            $submittedText = $body['contents'][0]['parts'][0]['text'];
            
            // Should be truncated to ~100 chars (plus prompt)
            return str_contains($submittedText, '[Content truncated');
        });
    }

    #[Test]
    public function groq_provider_is_not_configured_without_api_key()
    {
        config(['services.ai.groq.api_key' => null]);

        $provider = new GroqProvider();

        $this->assertFalse($provider->isConfigured());
    }

    #[Test]
    public function groq_provider_is_configured_with_api_key()
    {
        config(['services.ai.groq.api_key' => 'test-key']);

        $provider = new GroqProvider();

        $this->assertTrue($provider->isConfigured());
    }

    #[Test]
    public function groq_provider_returns_correct_names()
    {
        config([
            'services.ai.groq.api_key' => 'test-key',
            'services.ai.groq.model' => 'llama-3.3-70b-versatile',
        ]);

        $provider = new GroqProvider();

        $this->assertEquals('groq', $provider->getProviderName());
        $this->assertEquals('llama-3.3-70b-versatile', $provider->getModelName());
    }

    #[Test]
    public function groq_provider_sends_correct_request_format()
    {
        config([
            'services.ai.groq.api_key' => 'test-key',
            'services.ai.groq.model' => 'llama-3.3-70b-versatile',
            'services.ai.groq.max_chars' => 10000,
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Generated notes']]
                ]
            ], 200)
        ]);

        $provider = new GroqProvider();
        $result = $provider->generateNotes('Test PDF content', 'Test Material');

        $this->assertEquals('Generated notes', $result);

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            
            return $body['model'] === 'llama-3.3-70b-versatile'
                && isset($body['messages'])
                && count($body['messages']) === 2
                && $body['messages'][0]['role'] === 'system'
                && $body['messages'][1]['role'] === 'user';
        });
    }

    #[Test]
    public function providers_throw_exception_on_api_error()
    {
        config([
            'services.ai.gemini.api_key' => 'test-key',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'API Error'], 500)
        ]);

        $provider = new GeminiProvider();

        $this->expectException(\Exception::class);
        $provider->generateNotes('Test content', 'Test Material');
    }
}
