# Library :: AdoreMe\Factory
If you find bugs, or have ideas to implement, please contact Core Engineering Team.
This library was designed to be used across all nawe projects, especially for new microservices.

#### This library was designed to be used with `adore-me/logger` library.

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
        "url": "git@github.com:adore-me/factory.git",
        "no-api": true
    }
]
```

Run ```composer require adore-me/factory```, to install the latest version.

## Usage
A new factory should extend `AdoreMe\Models\FactoryAbstract` or just implement interface `AdoreMe\Interfaces\FactoryInterface`.
A new model that is returned by factory should extend `AdoreMe\Models\ModelAbstract` or just implement interface `AdoreMe\Interfaces\ModelInterface`
