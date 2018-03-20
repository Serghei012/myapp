<?php
namespace laravel\AdoreMe\Common\Models\NonPersistent;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Models\NonPersistentModel;

class ModelWithDefaults extends NonPersistentModel implements ModelInterface
{
    /**
     * Holds the default attributes and values for model.
     *
     * @var array
     */
    protected $defaultAttributesAndValues = [
        'flag' => true,
        'name' => 'some default name',
    ];
}
