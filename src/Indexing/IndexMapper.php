<?php

namespace SmartSearch\Indexing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IndexMapper
{
    public function schema(Model $model, string $analyzer = 'standard'): array
    {
        $properties = [];
        $fields = $model->getSearchableFields();

        foreach ($fields as $field) {
            $properties[$field] = $this->resolveFieldMapping($model, $field);
        }

        $properties['id'] = ['type' => 'keyword'];

        return [
            'index' => $model->getSearchIndexName(),
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

        foreach ($model->getSearchableFields() as $field) {
            $value = $model->{$field};
            $body[$field] = is_string($value)
                ? \SmartSearch\Support\ArabicNormalizer::normalize($value)
                : $value;
        }

        return [
            'index' => $model->getSearchIndexName(),
            'id' => (string) $model->getKey(),
            'body' => $body,
        ];
    }

    public function deletePayload(Model $model): array
    {
        return [
            'index' => $model->getSearchIndexName(),
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
