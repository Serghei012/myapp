<?php
namespace specIntrusive\AdoreMe\Common\Models\Eloquent;

use laravel\AdoreMe\Common\Models\Eloquent\EloquentTransientAttributes;
use PhpSpec\Laravel\LaravelObjectBehavior;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

/** @var EloquentTransientAttributes $this */
class EloquentTransientAttributesSpec extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait;

    public function let()
    {
        /** @var EloquentTransientAttributes $this */
        $this->beAnInstanceOf(EloquentTransientAttributes::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_saving_with_a_non_transient_attribute_should_throw_exception()
    {
        /** @var EloquentTransientAttributes $this */
        $data = [
            'test_transient'                  => 1,
            'test_transient_with_set_mutator' => 2,
            'test_transient_with_get_mutator' => 3,
            'name'                            => 'name',
            'non_existent_non_transient'      => 5,
        ];
        $this->setRawAttributes($data);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringSave();
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_saving_with_a_transient_attribute_should_work()
    {
        /** @var EloquentTransientAttributes $this */
        $data = [
            'test_transient'                  => 1,
            'test_transient_with_set_mutator' => 2,
            'test_transient_with_get_mutator' => 3,
            'name'                            => 'name',
        ];
        $this->setRawAttributes($data);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldNotThrow()->duringSave();
    }
}
