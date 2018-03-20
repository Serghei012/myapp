<?php
namespace spec\AdoreMe\Common\Models\NonPersistent;

use AdoreMe\Common\Models\NonPersistentModel;
use laravel\AdoreMe\Common\Models\NonPersistent\Model;
use laravel\AdoreMe\Common\Models\NonPersistent\ModelWithDefaults;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use spec\AdoreMe\Common\Traits\ModelTrait;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

/** @var Model $this */
class ModelSpec extends ObjectBehavior
{
    use ModelTrait;
    use PhpSpecMatchersTrait;

    public function let()
    {
        /** @var Model $this */
        $this->beAnInstanceOf(Model::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_stub_is_initializable()
    {
        /** @var Model $this */
        $this->shouldHaveType(NonPersistentModel::class);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_has_defaults()
    {
        /** @var ModelWithDefaults $this */
        $this->beAnInstanceOf(ModelWithDefaults::class);

        /** @var Subject $result */
        $result = $this->toArray();
        $result->shouldHaveKeyWithValue('flag', true);
        $result->shouldHaveKeyWithValue('name', 'some default name');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_has_defaults_overwritten()
    {
        /** @var ModelWithDefaults $this */
        $this->beAnInstanceOf(ModelWithDefaults::class);
        $this->beConstructedWith(['name' => 'name given from constructor']);

        /** @var Subject $result */
        $result = $this->toArray();
        $result->shouldHaveKeyWithValue('flag', true);
        $result->shouldHaveKeyWithValue('name', 'name given from constructor');
    }
}
