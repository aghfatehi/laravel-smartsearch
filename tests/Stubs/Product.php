<?php

namespace SmartSearch\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use SmartSearch\Traits\SmartSearchable;

class Product extends Model
{
    use SmartSearchable;

    protected $table = 'products';
    protected $guarded = [];
    public $timestamps = false;

    protected $smartSearchable = [
        'name',
        'description',
    ];
}
