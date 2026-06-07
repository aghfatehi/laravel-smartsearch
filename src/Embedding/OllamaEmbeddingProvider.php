<?php

namespace SmartSearch\Embedding;

use Illuminate\Support\Facades\Http;
use SmartSearch\Contracts\EmbeddingProvider;

class OllamaEmbeddingProvider implements EmbeddingProvider
{
    private string $host;
    private string $model;
    private int $timeout;
    private ?int $dimensions;

    public function __construct(array $config = [])
    {
        $this->host = rtrim($config['host'] ?? 'http://localhost:11434', '/');
        $this->model = $config['model'] ?? 'nomic-embed-text';
        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->dimensions = isset($config['dimensions']) ? (int) $config['dimensions'] : null;
    }

    public function embedText(string $text): array
    {
        try {
            $response = Http::timeout($this->timeout)->post("{$this->host}/api/embeddings", [
                'model' => $this->model,
                'prompt' => $text,
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('Ollama embedding request failed: ' . $response->body());
            }

            $data = $response->json();
            $embedding = $data['embedding'] ?? [];

            if (empty($embedding)) {
                throw new \RuntimeException('Ollama returned empty embedding');
            }

            return $embedding;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \RuntimeException('Ollama connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function embedTexts(array $texts): array
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $embeddings[] = $this->embedText($text);
        }

        return $embeddings;
    }

    public function dimensions(): int
    {
        if ($this->dimensions !== null) {
            return $this->dimensions;
        }

        $test = $this->embedText('test');
        $this->dimensions = count($test);

        return $this->dimensions;
    }
}
