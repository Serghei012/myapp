<?php
namespace spec\AdoreMe\Common\Helpers;

use AdoreMe\Common\Helpers\ArrayHelper;
use AdoreMe\Common\Interfaces\ModelInterface;
use AdoreMe\Common\Models\NonPersistentModel;
use Illuminate\Support\Collection;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;

/** @var ArrayHelper $this */
class ArrayHelperSpec extends ObjectBehavior
{
    /** @noinspection PhpMethodNamingConventionInspection */
    function it_filterValuesOrKeysWithNullData_works_as_expected_with_non_multi_dimensional_array()
    {
        /** @var ArrayHelper $this */
        $array = [
            'test 1' => 1,
            'test 2' => null,
            'test 3' => '',
        ];

        $expected = [
            'test 1' => 1,
            'test 3' => '',
        ];

        /** @var Subject $result */
        $result = $this->filterValuesOrKeysWithNullData($array);
        $result->shouldIterateAs($expected);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_filterValuesOrKeysWithNullData_works_as_expected_test_with_2_level_array()
    {
        /** @var ArrayHelper $this */
        $array = [
            'discount_stamp'    => [
                'stamp_image' => '239/stamp_1452703311.png',
            ],
            'info_box'          => [
                'special' => null,
            ],
            'category_image'    => null,
            'featured_on_block' => null,
            'featured_cta'      => '',
        ];

        $expected = [
            'discount_stamp' => [
                'stamp_image' => '239/stamp_1452703311.png',
            ],
            'featured_cta'   => '',
        ];

        /** @var Subject $result */
        $result = $this->filterValuesOrKeysWithNullData($array);
        $result->shouldIterateAs($expected);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_filterValuesOrKeysWithNullData_works_as_expected_test_2()
    {
        /** @var ArrayHelper $this */
        $array = [
            'im null 1' => null,
            'array 1'   => [
                'im null 2' => null,
                'array 2'   => [
                    'array 3'    => [
                        'array 4'    => [
                            'array 5' => [
                                'value'      => null,
                                'non null 5' => 5,
                            ],
                        ],
                        'im empty 3' => '',
                    ],
                    'non null 2' => 2,
                ],
            ],
        ];

        $expected = [
            'array 1' => [
                'array 2' => [
                    'array 3'    => [
                        'array 4'    => [
                            'array 5' => [
                                'non null 5' => 5,
                            ],
                        ],
                        'im empty 3' => '',
                    ],
                    'non null 2' => 2,
                ],
            ],
        ];

        /** @var Subject $result */
        $result = $this->filterValuesOrKeysWithNullData($array);
        $result->shouldIterateAs($expected);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_arrayClone_works_as_expected()
    {
        /** @var ArrayHelper $this */
        $array  = $this->spec_create_test_array_for_arrayClone_method();
        $array2 = $array;

        // Test that reference is working.
        $array2['test_7_std_class']->test_1 = 0.1;
        $array2['test_8_collection']->put('test_1', 0.2);
        $array2['test_9_non_persistent_model']->test_1 = 0.3;
        if ($array['test_7_std_class']->test_1 != 0.1 || $array2['test_7_std_class']->test_1 != 0.1) {
            throw new FailureException('Reference is not working on std class! Review the test!');
        } else if ($array['test_8_collection']->get('test_1') != 0.2
            || $array2['test_8_collection']->get('test_1')
            != 0.2
        ) {
            throw new FailureException('Reference is not working on Collection! Review the test!');
        } else if ($array['test_9_non_persistent_model']->test_1 != 0.3
            || $array2['test_9_non_persistent_model']->test_1 != 0.3
        ) {
            throw new FailureException('Reference is not working on non persistent model! Review the test!');
        }

        /** @var Subject $clonedArray */
        $clonedArray = $this->arrayClone($array);

        // Test that reference is still working.
        $clonedArray['test_7_std_class']->test_1->shouldBe(0.1);
        /** @noinspection PhpUndefinedMethodInspection */
        $clonedArray['test_8_collection']->get('test_1')->shouldBe(0.2);
        $clonedArray['test_9_non_persistent_model']->test_1->shouldBe(0.3);

        // Change the value from original array.
        $array['test_7_std_class']->test_1 = 0;
        $array['test_8_collection']->put('test_1', 0);
        $array['test_9_non_persistent_model']->test_1 = 0;
        if ($array['test_7_std_class']->test_1 != 0 || $array2['test_7_std_class']->test_1 != 0) {
            throw new FailureException('Reference is not working on std class! Review the test!');
        } else if ($array['test_8_collection']->get('test_1') != 0
            || $array2['test_8_collection']->get('test_1')
            != 0
        ) {
            throw new FailureException('Reference is not working on Collection! Review the test!');
        } else if ($array['test_9_non_persistent_model']->test_1 != 0
            || $array2['test_9_non_persistent_model']->test_1
            != 0
        ) {
            throw new FailureException('Reference is not working on non persistent model! Review the test!');
        }

        // Test that the value was not changed.
        $clonedArray['test_7_std_class']->test_1->shouldNotBe(0);
        /** @noinspection PhpUndefinedMethodInspection */
        $clonedArray['test_8_collection']->get('test_1')->shouldNotBe(0);
        $clonedArray['test_9_non_persistent_model']->test_1->shouldNotBe(0);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_hasObjectOrResource_should_return_true_on_array_that_has_models()
    {
        /** @var ArrayHelper $this */
        $arrayWithObjects = $this->spec_create_test_array_for_arrayClone_method();
        /** @var Subject $result */
        $result = $this->hasObjectOrResource($arrayWithObjects);
        $result->shouldBe(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_hasObjectOrResource_should_return_false_on_plain_simple_array_without_models()
    {
        /** @var ArrayHelper $this */
        /** @var Subject $result */
        $result = $this->hasObjectOrResource(
            [
                'test 1' => 1,
                'test 2' => null,
            ]
        );
        $result->shouldBe(false);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_hasObjectOrResource_should_return_true_on_array_that_has_resources()
    {
        /** @var ArrayHelper $this */
        /** @var Subject $result */
        $result = $this->hasObjectOrResource(
            [
                'test 1'   => 1,
                'test 2'   => null,
                'resource' => fopen("php://output", 'w'),
            ]
        );
        $result->shouldBe(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_hasObjectOrResource_should_return_true_on_multi_dimensional_array_that_has_resources()
    {
        /** @var ArrayHelper $this */
        /** @var Subject $result */
        $result = $this->hasObjectOrResource(
            [
                'test 1'  => 1,
                'test 2'  => null,
                'level 1' => [
                    'level 2' => [
                        'level 3' => [
                            'level 4' => [
                                'resource' => fopen("php://output", 'w'),
                            ],
                        ],
                    ],
                ],
            ]
        );
        $result->shouldBe(true);
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_implodeWithKeyAndValue_should_work()
    {
        /** @var ArrayHelper $this */
        /** @var Subject $result */
        $result = $this->implodeWithKeyAndValue(
            [
                'test'   => 'value',
                'test 2' => 'value 2',
                'test 3' => [
                    'value',
                    'value 2',
                ],
            ],
            ';',
            '=',
            null
        );
        $result->shouldBe('test=value;test 2=value 2;test 3[]=value&test 3[]=value 2');
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    function it_findAttributesFromCollection_should_work()
    {
        /** @var ArrayHelper $this */
        // Create std class.
        $stdClass       = new \stdClass();
        $stdClass->name = 'name from std class';

        // Create collection.
        $collection = Collection::make(
            [
                'name'       => 'name from collection',
                'collection' => Collection::make(
                    [
                        'name' => 'name from collection`s collection',
                    ]
                ),
            ]
        );

        // Create non persistent model.
        $nonPersistentModel = new class(['name' => 'name from non persistent model']) extends NonPersistentModel
        {
        };

        // Final collection.
        $collection = Collection::make(
            [
                'value',
                [
                    'something' => 'value',
                ],
                [
                    'name' => 'name from array',
                    'test' => [
                        'test' => 'value',
                    ],
                ],
                [
                    'name' => 'name from array duplicate',
                ],
                [
                    'name' => 'name from array duplicate',
                ],
                Collection::make(['something' => 'value']),
                $stdClass,
                $collection,
                $nonPersistentModel,
                new class()
                {
                },
                new class()
                {
                    public $name = 'name from anonymous 1';
                },
                new class()
                {
                    protected $name = 'name from anonymous 2';
                },
                new class()
                {
                    public function __get($key)
                    {
                        if ($key == 'name') {
                            return 'name from anonymous 3';
                        }

                        return null;
                    }
                },
            ]
        );

        /** @var Subject $result */
        $result = $this->findAttributesFromCollection('name', $collection);
        $result->shouldIterateAs(
            [
                'name from array',
                'name from array duplicate',
                'name from array duplicate',
                'name from std class',
                'name from collection',
                'name from non persistent model',
            ]
        );
    }

    /** @noinspection PhpMethodNamingConventionInspection */
    protected function spec_create_test_array_for_arrayClone_method()
    {
        $testArray = [
            'test_1' => 0,
            'test_2' => 1,
            'test_3' => '0',
            'test_4' => '1',
            'test_5' => 'string',
            'test_6' => null,
        ];

        // Create std class.
        $stdClass = new \stdClass();
        // Fill the objects with data.
        foreach ($testArray as $key => $value) {
            $stdClass->{$key} = $value;
        }

        // Create collection.
        $collection = Collection::make($testArray);

        // Create non persistent model.
        $nonPersistentModel = new class($testArray) extends NonPersistentModel
        {
        };

        // Inject the other models to the other models.
        $stdClass->collection           = $collection;
        $stdClass->non_persistent_model = $nonPersistentModel;
        $collection->put('std_class', $stdClass);
        $collection->put('non_persistent_model', $nonPersistentModel);
        /** @noinspection PhpUndefinedFieldInspection */
        $nonPersistentModel->std_class = $stdClass;
        /** @noinspection PhpUndefinedFieldInspection */
        $nonPersistentModel->collection = $collection;

        $testArray = array_merge(
            $testArray,
            [
                'test_7_std_class'            => $stdClass,
                'test_8_collection'           => $collection,
                'test_9_non_persistent_model' => $nonPersistentModel,
            ]
        );

        return $testArray;
    }
}
