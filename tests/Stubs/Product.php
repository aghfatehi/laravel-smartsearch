<?php

namespace SmartSearch\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use SmartSearch\Traits\Searchable;

class Product extends Model
{
    use Searchable;

    protected $table = 'products';
    protected $guarded = [];
    public $timestamps = false;

    protected $searchable = [
        'name',
        'description',
    ];
}
