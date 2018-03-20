<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use laravel\AdoreMe\Storage\Models\DummyStoreClassTest;
use laravel\AdoreMe\Storage\Models\SimpleModel;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Laravel\LaravelObjectBehavior;
use PhpSpec\Wrapper\Subject;
use stubs\AdoreMe\Common\Traits\PhpSpecMatchersTrait;

abstract class AbstractTests extends LaravelObjectBehavior
{
    use PhpSpecMatchersTrait {
        getMatchers as oldGetMatchers;
    }

    protected $specNewNamespace = 'tests_2';

    /** @var int */
    protected $dummyModelValue = 5;

    /** @var string */
    protected $specStoreNamespace = 'tests';

    /** @var string */
    protected $specStoreGlue = ':';

    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_is_initializable();

    /** @noinspection PhpMethodNamingConventionInspection */
    abstract function it_has_active_testable_connection();

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ADD_should_return_true()
    {
        /** @var StoreInterface $this */
        $this->forever('test_key', 'test_value');

        /** @var Subject $result */
        $result = $this->add('test_key2', 'test_value2', 100);
        $result->shouldReturn(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_ADD_should_return_false()
    {
        /** @var StoreInterface $this */
        $this->forever('test_key2', 'test_value2');

        /** @var Subject $result */
        $result = $this->add('test_key2', 'test_value2', 100);
        $result->shouldReturn(false);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_multiple_PUT_and_MANY_should_work()
    {
        /** @var StoreInterface $this */
        $testValues = $this->getTestValues();
        foreach ($testValues as $key => $value) {
            $this->put($key, $value, 1);
        }

        /** @var Subject $result */
        $result = $this->many(array_keys($testValues));
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnArrayWithKeyValues($testValues);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_PUT_MANY_should_always_return_the_correct_value_and_data_type_on_MANY()
    {
        /** @var StoreInterface $this */
        $testValues = $this->getTestValues();

        $this->putMany($testValues);
        /** @var Subject $result */
        $result = $this->many(array_keys($testValues));
        /** @noinspection PhpUndefinedMethodInspection */
        $result->shouldReturnArrayWithKeyValues($testValues);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_PUT_should_always_return_the_correct_value_and_data_type_on_GET()
    {
        /** @var StoreInterface $this */
        $testValues = $this->getTestValues();
        foreach ($testValues as $key => $value) {
            $this->put($key, $value, 1);

            if ($key == 'array_with_model') {
                /** @var DummyStoreClassTest|Subject $model */
                $model = $this->get($key)['model'];
                /** @var Subject $result */
                $result = $model->get();
                $result->shouldReturn($this->dummyModelValue);
            } elseif ($key == 'model') {
                /** @var DummyStoreClassTest|Subject $model */
                $model = $this->get($key);
                /** @var Subject $result */
                $result = $model->get();
                $result->shouldReturn($this->dummyModelValue);
            } else {
                /** @var Subject $result */
                $result = $this->get($key);
                $result->shouldReturn($value);
            }
        }
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_GET_a_non_existent_key_should_return_null_value()
    {
        /** @var StoreInterface $this */
        /** @var Subject $a */
        $a = $this->get('a');
        $a->shouldReturn(null);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_HAS_a_non_existent_key_should_return_false_value()
    {
        /** @var StoreInterface $this */
        /** @var Subject $a */
        $a = $this->has('a');
        $a->shouldReturn(false);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_HAS_with_an_existing_key_should_return_true()
    {
        /** @var StoreInterface $this */
        $this->put('a', 'some random string', 1);
        /** @var Subject $a */
        $a = $this->has('a');
        $a->shouldReturn(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_INCREMENT_with_1_return_integer_1()
    {
        /** @var StoreInterface $this */
        /** @var Subject $increment */
        $increment = $this->increment('a', 1);
        $increment->shouldReturn(1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_INCREMENT_existing_key_that_has_value_1_with_1_return_integer_2()
    {
        /** @var StoreInterface $this */
        $this->increment('a', 1);
        /** @var Subject $increment */
        $increment = $this->increment('a', 1);
        $increment->shouldReturn(2);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_INCREMENT_multiple_times_should_work_as_intended()
    {
        /** @var StoreInterface $this */
        $this->increment('a', 1);
        $this->increment('a', 1);
        /** @var Subject $increment */
        $increment = $this->increment('a', 1);
        $increment->shouldReturn(3);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_INCREMENT_with_1_and_GET_should_work_and_return_integer_1()
    {
        /** @var StoreInterface $this */
        $this->increment('a', 1);
        /** @var Subject $a */
        $a = $this->get('a');
        $a->shouldReturn(1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_INCREMENT_with_1_on_a_key_added_with_put_and_value_1_should_return_1()
    {
        /** @var StoreInterface $this */
        $this->put('a', 1, 5);
        /** @var Subject $increment */
        $increment = $this->increment('a', 1);
        $increment->shouldReturn(2);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_PUT_with_random_string_and_then_INCREMENT_a_with_1_should_not_work_and_return_exception()
    {
        /** @var StoreInterface $this */
        $this->put('a', 'some random string, non related to a number whatsoever', 1);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringIncrement('a', 1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_DECREMENT_multiple_times_should_work_as_intended_to_0()
    {
        /** @var StoreInterface $this */
        $this->put('a', 3);
        $this->decrement('a', 1);
        $this->decrement('a', 1);
        /** @var Subject $decrement */
        $decrement = $this->decrement('a', 1);
        $decrement->shouldReturn(0);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_DECREMENT_with_1_on_a_key_added_with_put_and_value_1_should_return_0()
    {
        /** @var StoreInterface $this */
        $this->put('a', 1, 5);
        /** @var Subject $decrement */
        $decrement = $this->decrement('a', 1);
        $decrement->shouldReturn(0);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_PUT_with_random_string_and_then_DECREMENT_should_not_work()
    {
        /** @var StoreInterface $this */
        $this->put('a', 'some random string, non related to a number whatsoever', 1);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->shouldThrow()->duringDecrement('a', 1);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_HAS_MANY_should_work()
    {
        /** @var StoreInterface $this */
        $this->put(
            'test_key',
            'test_value',
            1
        );

        $this->put(
            'test_key',
            'test_value',
            1
        );

        $this->put(
            'test_key3',
            'test_value3',
            1
        );

        /** @var Subject $result */
        $result = $this->hasMany(
            [
                'test_key',
                'test_key2',
                'test_key3',
                'test_key4',
            ]
        );
        $result->shouldIterateAs(
            [
                'test_key'  => true,
                'test_key2' => false,
                'test_key3' => true,
                'test_key4' => false,
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_PUT_a_key_and_then_FORGET_that_key_should_return_false_upon_HAS_on_that_key_and_true_upon_another_existing_key(
    )
    {
        /** @var StoreInterface $this */
        $this->put(
            'test_key',
            'test_value',
            100
        );

        $this->put(
            'test_key2',
            'test_value2',
            100
        );

        $this->forget('test_key');

        /** @var Subject $result */
        $result = $this->has('test_key');
        $result->shouldBe(false);

        /** @var Subject $result */
        $result = $this->has('test_key2');
        $result->shouldBe(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_a_model_retrieved_from_storage_that_is_modified_should_not_also_modify_by_reference_the_cache()
    {
        /** @var StoreInterface $this */
        $model = new SimpleModel('original');
        $this->put('model', $model);

        /** @var SimpleModel|Subject $modelFromCache */
        $modelFromCache = $this->get('model');
        $modelFromCache->name->shouldBe('original');
        $modelFromCache->name = 'updated';

        // Verify that a new model retrieved from cache, does not have its value changed.
        /** @var SimpleModel|Subject $newModelFromCache */
        $newModelFromCache = $this->get('model');
        $newModelFromCache->name->shouldBe('original');
    }

    /**
     * @return array
     */
    protected function getTestValues(): array
    {
        $dummyModel = new DummyStoreClassTest();
        $dummyModel->set($this->dummyModelValue);

        return [
            'a'                => null,
            'b'                => 'null',
            'c'                => '',
            'd'                => false,
            'e'                => 0,
            'f'                => '0',
            'g'                => true,
            'h'                => 1,
            'i'                => '1',
            'j'                => -1,
            'k'                => 1.5,
            'l'                => '1.5',
            'm'                => 0.33,
            'n'                => '0.33',
            'o'                => ['name' => 'test', 'number' => 1],
            'array_with_model' => ['name' => 'test', 'number' => 1, 'model' => $dummyModel],
            'model'            => $dummyModel,
        ];
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return bool
     * @throws FailureException
     */
    function checkIfArrayIsIdentical(array $array1, array $array2)
    {
        if (array_keys($array1) != array_keys($array2)) {
            return false;
        }

        foreach ($array1 as $key => $item) {
            $array1Values = $array1[$key];
            $array2Values = $array2[$key];
            if ($array1Values !== $array2Values) {

                if (
                    is_array($array1Values)
                    && is_array($array2Values)
                    && $this->checkIfArrayIsIdentical(
                        $array1Values,
                        $array2Values
                    )
                ) {
                    continue;
                } else if (
                    is_object($array1Values)
                    && is_object($array2Values)
                    && $array1Values == $array2Values
                ) {
                    continue;
                }

                throw new FailureException(
                    'Key "'
                    . $key
                    . '" does not match. Received "' . PHP_EOL
                    . print_r($array1Values, true)
                    . '", expected "' . PHP_EOL
                    . print_r($array2Values, true)
                    . '".'
                );
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMatchers()
    {
        return array_merge(
            $this->oldGetMatchers(),
            [
                'returnArrayValues'        => function (array $subject, array $values) {
                    sort($subject);
                    sort($values);

                    return $subject === $values;
                },
                'returnArrayWithKeyValues' => function (array $subject, array $values) {
                    return $this->checkIfArrayIsIdentical($subject, $values);
                },
            ]
        );
    }

    /**
     * Change the namespace via reflection.
     *
     * @param string $namespace
     */
    protected function specChangeNamespaceViaReflection($namespace)
    {
        /** @var StoreInterface|Subject $this */
        $model      = $this->getWrappedObject();
        $reflection = new \ReflectionClass($model);
        $property   = $reflection->getProperty('prefix');
        $property->setAccessible(true);
        $property->setValue($model, $namespace);
    }
}
