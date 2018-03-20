# Library :: AdoreMe\Library\Fixtures
If you find bugs, or have ideas to implement, please contact Core Engineering Team.
This library was designed to be used across all nawe projects, especially for new microservices.

## Change log
See [CHANGELOG.md](/CHANGELOG.md).

## What it does?
Read [CHANGELOG.md](/CHANGELOG.md) to learn what this library can provide.
For extra info, dig deep into the code.

## Installation
Edit composer.json and add the following lines:
```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:adore-me/lib-fixtures.git",
        "no-api": true
    }
]
```

Run ```composer require adore-me/lib-fixtures```, to install the latest version.

## Usage
- Insert `AdoreMe\Library\Fixtures\Providers\LibraryFixturesProvider::class` to your `app.php` laravel configuration, in `providers` section.
- Create in your root project folder `tests\fixtures\templates\`, and create the php template there (`template_name.php`), that returns an array with configuration.
- Call via url: `http://url/tools/fixtures/apply/template_name` to apply the template described/configured in `template_name.php`.

Notes:
- the repository class must be instantiable via Laravel's IoC. Usually should be the interface.
- additional model and/or repository commands can be called, before creating the items.
- the repository and model must follow `adore-me/common` interfaces.

## Simple template example
````php
<?php
use AdoreMe\Library\Fixtures\Services\LibraryFixturesService;
use laravel\AdoreMe\Library\Fixtures\Interfaces\ProductRepositoryInterface;

return [
    [
        LibraryFixturesService::CONFIG_REPOSITORY_CLASS => ProductRepositoryInterface::class,
        LibraryFixturesService::CONFIG_ITEMS            => [
            [
                'name' => 'name 1',
            ],
            [
                'name' => 'name 2',
            ],
        ],
    ],
];
?>
````

## Complex template example, with model and repository init commands.
````php
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
];
?>
````