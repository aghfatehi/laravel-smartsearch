<?php

namespace SmartSearch\Contracts;

interface EmbeddingProvider
{
    public function embedText(string $text): array;

    public function embedTexts(array $texts): array;

    public function dimensions(): int;
}
