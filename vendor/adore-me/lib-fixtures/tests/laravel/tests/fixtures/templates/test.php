<?php
use AdoreMe\Library\Fixtures\Services\LibraryFixturesService;
use laravel\AdoreMe\Library\Fixtures\Interfaces\ProductRepositoryInterface;

return [
    [
        LibraryFixturesService::CONFIG_REPOSITORY_CLASS => ProductRepositoryInterface::class,
        LibraryFixturesService::CONFIG_MODEL_INIT       => [
            [
                LibraryFixturesService::CONFIG_MODEL_INIT_COMMAND            => 'unguard',
                LibraryFixturesService::CONFIG_MODEL_INIT_COMMAND_PARAMETERS => [true],
            ],
        ],
        LibraryFixturesService::CONFIG_REPOSITORY_INIT  => [
            [
                LibraryFixturesService::CONFIG_REPOSITORY_INIT_COMMAND            => 'createModel',
                LibraryFixturesService::CONFIG_REPOSITORY_INIT_COMMAND_PARAMETERS => [
                    [
                        'id'   => 2,
                        'name' => 'created by init repository',
                    ],
                ],
            ],
        ],
        LibraryFixturesService::CONFIG_ITEMS            => [
            [
                'id'   => 5,
                'name' => 'name with id 5',
            ],
            [
                'id'   => 4,
                'name' => 'name with id 4',
            ],
        ],
    ],

    [
        LibraryFixturesService::CONFIG_REPOSITORY_CLASS => ProductRepositoryInterface::class,
        LibraryFixturesService::CONFIG_MODEL_INIT       => [
            [
                LibraryFixturesService::CONFIG_MODEL_INIT_COMMAND            => 'unguard',
                LibraryFixturesService::CONFIG_MODEL_INIT_COMMAND_PARAMETERS => [true],
            ],
        ],
        LibraryFixturesService::CONFIG_REPOSITORY_INIT  => [
            [
                LibraryFixturesService::CONFIG_REPOSITORY_INIT_COMMAND            => 'createModel',
                LibraryFixturesService::CONFIG_REPOSITORY_INIT_COMMAND_PARAMETERS => [
                    [
                        'name' => 'created by init repository',
                    ],
                ],
            ],
        ],
        LibraryFixturesService::CONFIG_ITEMS            => [
            [
                'name' => 'name with id 5',
            ],
            [
                'name' => 'name with id 4',
            ],
        ],
    ],

    [
        LibraryFixturesService::CONFIG_REPOSITORY_CLASS => ProductRepositoryInterface::class,
        LibraryFixturesService::CONFIG_MODEL_INIT       => [
            [
                LibraryFixturesService::CONFIG_MODEL_INIT_COMMAND            => 'unguard',
                LibraryFixturesService::CONFIG_MODEL_INIT_COMMAND_PARAMETERS => [true],
            ],
        ],
        LibraryFixturesService::CONFIG_REPOSITORY_INIT  => [
            [
                LibraryFixturesService::CONFIG_REPOSITORY_INIT_COMMAND            => 'createModel',
                LibraryFixturesService::CONFIG_REPOSITORY_INIT_COMMAND_PARAMETERS => [
                    [
                        'name' => 'created by init repository',
                    ],
                ],
            ],
        ],
        LibraryFixturesService::CONFIG_ITEMS            => [
            [
                'name' => 'name with id 5',
            ],
            [
                'name' => 'name with id 4',
            ],
        ],
    ],
];
