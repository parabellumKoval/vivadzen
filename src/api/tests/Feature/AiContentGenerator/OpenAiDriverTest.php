<?php

namespace Tests\Feature\AiContentGenerator;

use Illuminate\Support\Facades\Http;
use ParabellumKoval\AiContentGenerator\DTO\GenerationRequest;
use ParabellumKoval\AiContentGenerator\Services\Drivers\OpenAiDriver;
use Tests\TestCase;

class OpenAiDriverTest extends TestCase
{
    public function test_gpt_5_models_use_max_completion_tokens(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'model' => 'gpt-5.1-2025-11-13',
                'choices' => [
                    ['message' => ['content' => 'ok']],
                ],
                'usage' => [],
            ]),
        ]);

        $driver = new OpenAiDriver([
            'api_key' => 'test-key',
            'base_uri' => 'https://api.openai.com/v1',
            'default_model' => 'gpt-5.1',
        ]);

        $driver->generate(new GenerationRequest(
            prompt: 'Rewrite this prompt.',
            model: 'gpt-5.1',
            maxTokens: 500,
        ));

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.openai.com/v1/chat/completions'
                && ($body['max_completion_tokens'] ?? null) === 500
                && !array_key_exists('max_tokens', $body);
        });
    }

    public function test_non_gpt_5_models_keep_max_tokens(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'model' => 'gpt-4o-mini',
                'choices' => [
                    ['message' => ['content' => 'ok']],
                ],
                'usage' => [],
            ]),
        ]);

        $driver = new OpenAiDriver([
            'api_key' => 'test-key',
            'base_uri' => 'https://api.openai.com/v1',
            'default_model' => 'gpt-4o-mini',
        ]);

        $driver->generate(new GenerationRequest(
            prompt: 'Rewrite this prompt.',
            model: 'gpt-4o-mini',
            maxTokens: 500,
        ));

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.openai.com/v1/chat/completions'
                && ($body['max_tokens'] ?? null) === 500
                && !array_key_exists('max_completion_tokens', $body);
        });
    }
}
