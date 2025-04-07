<?php

namespace Sigawa\Sigawax\Core\AI\Adapters;

use GuzzleHttp\Client;
use Sigawa\Sigawax\Core\Contracts\Interface\AI\AIAdapterInterface;

class OpenAIAdapter implements AIAdapterInterface
{
    protected ?string $apiKey;
    protected ?Client $client;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey;

        if ($this->isEnabled()) {
            $this->client = new Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
            ]);
        }
    }

    public function isEnabled(): bool
    {
        return !empty($this->apiKey);
    }

    protected function callOpenAI(string $prompt): string
    {
        if (!$this->isEnabled()) {
            return 'AI integration not enabled.';
        }

        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful PHP and architecture assistant.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);
            return $body['choices'][0]['message']['content'] ?? 'No response from AI.';
        } catch (\Throwable $e) {
            return "AI request failed: " . $e->getMessage();
        }
    }

    public function suggest(string $context = ''): array
    {
        if ($this->isEnabled()) {
            $result = $this->callOpenAI("Suggest useful PHP services or architectural tools for the context: \"$context\"");
            return explode("\n", trim($result));
        }

        // Fallback local logic
        return match (true) {
            str_contains(strtolower($context), 'http') => ['HttpClientInterface', 'RequestLogger'],
            str_contains(strtolower($context), 'db') => ['DatabaseManager', 'ConnectionResolver'],
            str_contains(strtolower($context), 'cache') => ['CacheManager', 'RedisAdapter'],
            str_contains(strtolower($context), 'ai') => ['PromptBuilder', 'LLMService'],
            default => ['Logger', 'Config', 'EventBus'],
        };
    }

    public function predict(string $namespace): array
    {
        if ($this->isEnabled()) {
            $result = $this->callOpenAI("Predict likely PHP classes under the namespace \"$namespace\".");
            return explode("\n", trim($result));
        }

        // Local fallback
        return [
            $namespace . '\\ServiceA',
            $namespace . '\\HelperB',
        ];
    }

    public function suggestRefactor(string $class): array
    {
        if ($this->isEnabled()) {
            $result = $this->callOpenAI("Suggest refactoring improvements for the PHP class \"$class\".");
            return explode("\n", trim($result));
        }

        return ['Split into smaller methods', 'Extract interface', 'Add docblocks'];
    }

    public function explainDependencies(string $abstract, bool $withAI = true): string
    {
        if ($withAI && $this->isEnabled()) {
            return $this->callOpenAI("Explain the constructor dependencies and design implications of the PHP class \"$abstract\".");
        }

        return "$abstract depends on multiple classes. Consider decoupling using interfaces or service containers.";
    }

    public function analyze(string $code): string
    {
        if ($this->isEnabled()) {
            return $this->callOpenAI("Analyze the following PHP code for quality, architecture, and design issues:\n\n$code");
        }

        return "Code could benefit from better separation of concerns and dependency injection.";
    }

    public function complete(string $prompt): string
    {
        if ($this->isEnabled()) {
            return $this->callOpenAI($prompt);
        }

        return "You could add logging middleware or consider extracting logic into services.";
    }
}