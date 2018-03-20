<?php
namespace laravel\AdoreMe\Common\Models\Eloquent;

use AdoreMe\Common\Interfaces\ModelInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property int id
 * @property string name
 */
class Model extends EloquentModel implements ModelInterface
{
    protected $fillable = [
        'name'
    ];

    protected $table = 'tests';
}
