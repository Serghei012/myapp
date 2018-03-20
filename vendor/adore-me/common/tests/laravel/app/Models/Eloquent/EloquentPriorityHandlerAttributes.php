<?php
namespace laravel\AdoreMe\Common\Models\Eloquent;

use AdoreMe\Common\Traits\Eloquent\EloquentPriorityHandlerAttributesTrait;
use AdoreMe\Common\Interfaces\ModelInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property int id
 * @property string name
 * @property string code
 * @property string title
 * @property array extra
 */
class EloquentPriorityHandlerAttributes extends EloquentModel implements ModelInterface
{
    use EloquentPriorityHandlerAttributesTrait;
    const NAME  = 'name';
    const CODE  = 'code';
    const TITLE = 'title';
    const EXTRA = 'extra';

    protected $casts = [
        self::NAME  => 'string',
        self::CODE  => 'string',
        self::TITLE => 'string',
        self::EXTRA => 'array',
    ];

    protected $fillable = [
        self::NAME,
        self::CODE,
        self::TITLE,
        self::EXTRA,
    ];

    protected $table = 'tests';

    /**
     * ModelWithEloquentPriorityHandlerAttributesTrait constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->initEloquentPriorityHandlerAttributesTrait();

        parent::__construct($attributes);
    }
}
