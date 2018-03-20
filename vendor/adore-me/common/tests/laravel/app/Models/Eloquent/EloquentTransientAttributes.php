<?php
namespace laravel\AdoreMe\Common\Models\Eloquent;

use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Traits\Eloquent\EloquentTransientAttributesTrait;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property int id
 * @property string name
 * @property string test_transient
 * @property string test_transient_with_set_mutator
 * @property string test_transient_with_get_mutator
 */
class EloquentTransientAttributes extends EloquentModel implements ModelInterface
{
    use EloquentTransientAttributesTrait;
    const TEST_TRANSIENT                        = 'test_transient';
    const TEST_TRANSIENT_WITH_SET_MUTATOR       = 'test_transient_with_set_mutator';
    const TEST_TRANSIENT_WITH_GET_MUTATOR       = 'test_transient_with_get_mutator';
    const TEST_TRANSIENT_WITH_OTHER_GET_MUTATOR = 'test_transient_with_other_get_mutator';
    const TEST_TRANSIENT_APPENDED_ATTRIBUTE     = 'test_transient_appended_attribute';

    public static $mutatedOnSetTransientValue = 128;

    public static $mutatedOnGetTransientValue = 256;

    protected $casts = [
        'name'                                      => 'string',
        self::TEST_TRANSIENT                        => 'string',
        self::TEST_TRANSIENT_WITH_SET_MUTATOR       => 'string',
        self::TEST_TRANSIENT_WITH_GET_MUTATOR       => 'string',
        self::TEST_TRANSIENT_WITH_OTHER_GET_MUTATOR => 'bool',
    ];

    protected $table = 'tests';

    // Make public, so we can access it for testing.
    public $appends = [
        self::TEST_TRANSIENT_WITH_OTHER_GET_MUTATOR,
    ];

    /**
     * ModelWithTraits constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->initEloquentTransientAttributesTrait(
            [
                self::TEST_TRANSIENT,
                self::TEST_TRANSIENT_WITH_SET_MUTATOR,
                self::TEST_TRANSIENT_WITH_GET_MUTATOR,
                self::TEST_TRANSIENT_WITH_OTHER_GET_MUTATOR,
                self::TEST_TRANSIENT_APPENDED_ATTRIBUTE,
            ]
        );

        parent::__construct($attributes);
    }

    public function setTestTransientWithSetMutatorAttribute()
    {
        $this->setRawTransientAttribute(self::TEST_TRANSIENT_WITH_SET_MUTATOR, self::$mutatedOnSetTransientValue);
    }

    /**
     * @return int
     */
    public function getTestTransientWithGetMutatorAttribute()
    {
        return self::$mutatedOnGetTransientValue;
    }

    /**
     * @return bool
     */
    public function getTestTransientWithOtherGetMutatorAttribute()
    {
        return true;
    }
}
