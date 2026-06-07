<?php

namespace SmartSearch\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ScoutProduct extends Model
{
    use Searchable;

    protected $table = 'products';
    protected $guarded = [];
    public $timestamps = false;

    public function searchableAs(): string
    {
        return 'products_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
        ];
    }
}
