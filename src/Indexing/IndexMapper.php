<?php

namespace SmartSearch\Indexing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IndexMapper
{
    public function schema(Model $model, string $analyzer = 'standard', ?int $vectorDimensions = null): array
    {
        $properties = [];
        $fields = $model->getSmartSearchableFields();

        foreach ($fields as $field) {
            $properties[$field] = $this->resolveFieldMapping($model, $field);
        }

        $properties['id'] = ['type' => 'keyword'];

        if ($vectorDimensions !== null) {
            $properties['embedding'] = [
                'type' => 'dense_vector',
                'dims' => $vectorDimensions,
            ];
        }

        return [
            'index' => $model->getSmartSearchIndexName(),
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'smartsearch_analyzer' => [
                                'type' => $analyzer,
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => $properties,
                ],
            ],
        ];
    }

    public function document(Model $model): array
    {
        $body = ['id' => (string) $model->getKey()];

        foreach ($model->getSmartSearchableFields() as $field) {
            $value = $model->{$field};
            $body[$field] = is_string($value)
                ? \SmartSearch\Support\ArabicNormalizer::normalize($value)
                : $value;
        }

        return [
            'index' => $model->getSmartSearchIndexName(),
            'id' => (string) $model->getKey(),
            'body' => $body,
        ];
    }

    public function deletePayload(Model $model): array
    {
        return [
            'index' => $model->getSmartSearchIndexName(),
            'id' => (string) $model->getKey(),
        ];
    }

    private function resolveFieldMapping(Model $model, string $field): array
    {
        $castType = $this->getCastType($model, $field);

        return match ($castType) {
            'integer', 'int' => ['type' => 'integer'],
            'float', 'double', 'decimal' => ['type' => 'float'],
            'boolean', 'bool' => ['type' => 'boolean'],
            'datetime', 'timestamp' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||epoch_millis'],
            'array', 'json' => ['type' => 'object'],
            default => ['type' => 'text'],
        };
    }

    private function getCastType(Model $model, string $field): ?string
    {
        $casts = $model->getCasts();
        return $casts[$field] ?? null;
    }
}
