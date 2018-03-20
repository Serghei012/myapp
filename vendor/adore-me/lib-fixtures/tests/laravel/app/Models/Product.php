<?php
namespace laravel\AdoreMe\Library\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use laravel\AdoreMe\Library\Fixtures\Interfaces\ProductInterface;

class Product extends Model implements ProductInterface
{
    protected $fillable = [
        'name',
    ];
}
