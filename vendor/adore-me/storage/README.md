# Library :: AdoreMe\Storage
If you find bugs, or have ideas to implement, please contact Core Engineering Team.
This library was designed to be used across all nawe projects, especially for new microservices.

#### This library was designed to be used with `adore-me/common` library.

## Contains:
- Redis (with true tag support).
- Memcached (no tag support - will throw exception if tags are attempted).
- Two Level Cache (data in fast store, tags in slow store, usually redis is slow store, and memcached fast store).
- PhpStaticVariable (store the data in a static php variable, ready to be re-used in the same instance of php).

# Limitations
- Memcached counter cannot go negative. This means that `$memcached->set('a', 0);` and `$memcached->decr('a')` will change nothing, key `a` will remain 0.
- By design `PhpStaticVariableStore` is a volatile storage, used only for that session of php. This means the seconds to expire a key is ignored/not used. We were thinking to throw an exception if expiration time is attempted, but we think is overkill. If you use this storage, you clearly know why and how.

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
        "url": "git@github.com:adore-me/storage.git",
        "no-api": true
    }
]
```

Run `composer require adore-me/storage`, to install the latest version.

## Provider
Update your `config/app.php` and insert `AdoreMe\Storage\Providers\StorageProvider::class` into `providers` key.
Example:
```php
'providers' => [
    ...
    AdoreMe\Storage\Providers\StorageProvider::class,
    ...
],
```
This will provide:
- Memcached store and repository.
- Redis store and repository.
- PhpStaticVariable store and repository.
- Two Level Cache store and repository, constructed with Redis as slow backend, and Memcached as fast backend.
- Default store set to Two Level Cache store, and default repository set to Two Level Cache repository.
- Configuration for redis and memcached are the same as of `Laravel 5.4`. See bellow.

## Usage
```php
$twoLevelCacheRepository = AdoreMe\Common\Helpers\ProviderHelper::make(AdoreMe\Storage\Interfaces\TwoLevelCacheRepositoryInterface::class);
$twoLevelCacheRepository->put('key', 'value', 5, ['tag 1', 'tag 2']);
```
The above example will put in two level cache the key `key` with value `value`, for 5 minutes, having tags `tag 1` and `tag 2`.

Available repositories:
- Repositories are to be used solely caching purposes.
- `AdoreMe\Storage\Interfaces\MemcachedRepositoryInterface::class`
  - Supports sasl, persistent connection and memcached options. See configuration bellow for details.
- `AdoreMe\Storage\Interfaces\RedisRepositoryInterface::class`
  - Supports as client both `Predis` and `PhpRedis`. See configuration bellow for details.
- `AdoreMe\Storage\Interfaces\PhpStaticVariableRepositoryInterface::class`
- `AdoreMe\Storage\Interfaces\TwoLevelCacheRepositoryInterface::class`
- `AdoreMe\Storage\Interfaces\RepositoryInterface::class`

Available stores:
- Stores are to be used as database, or when you want to create your own caching logic.
- `AdoreMe\Storage\Interfaces\MemcachedStoreInterface::class`
- `AdoreMe\Storage\Interfaces\RedisStoreInterface::class`
- `AdoreMe\Storage\Interfaces\PhpStaticVariableStoreInterface::class`
- `AdoreMe\Storage\Interfaces\TwoLevelCacheStoreInterface::class`
- `AdoreMe\Storage\Interfaces\StoreInterface::class`

Notes:
- As NoSQL database you will want to use Redis with persistence. The default Redis of this library should be the one without persistence, as it used for caching, and not database.
- If you want multiple providers with different servers/connections for redis and memcached, you will need to create new service providers, and instantiate the stores with new configuration. Or simply inside the service provider that instantiate your class, make the new Redis/Memcached with desired configuration. See [StorageProvider.ph](/src/Providers/StorageProvider.php) for examples. 

## Configuration
In `config/cache.php`.
```php
return [
    ...
    'stores' => [
        ...
        'memcached' => [
            'prefix'        => 'memcached_prefix',
            'driver'        => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl'          => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options'       => [
                // Memcached::OPT_CONNECT_TIMEOUT  => 2000,
            ],
            'servers'       => [
                [
                    'host'   => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port'   => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
        ...
        'memcached_session' => [
            'prefix'        => 'memcached_session_prefix',
            'driver'        => 'memcached',
            'persistent_id' => env('MEMCACHED_SESSION_PERSISTENT_ID'),
            'sasl'          => [
                env('MEMCACHED_SESSION_USERNAME'),
                env('MEMCACHED_SESSION_PASSWORD'),
            ],
            'options'       => [
                // Memcached::OPT_CONNECT_TIMEOUT  => 2000,
            ],
            'servers'       => [
                [
                    'host'   => env('MEMCACHED_SESSION_HOST', '127.0.0.1'),
                    'port'   => env('MEMCACHED_SESSION_PORT', 11212),
                    'weight' => 100,
                ],
            ],
        ],
        ...
        'redis' => [
            'prefix'     => 'redis_prefix',
            'driver'     => 'redis',
            'connection' => 'default',
        ],
        ...
        'php_static_variable' => [
            'prefix'     => 'php_static_variable_prefix',
        ],
        ...
        'two_level_cache' => [
            'prefix'     => 'two_level_cache_prefix',
        ]
        ...
    ]
    ...
    'prefix' => env('CACHE_PREFIX', 'adore_me_storage')
    ...
]
```

In `config/database.php`
```php
return [
    ...
    'redis' => [
        'client'      => 'predis',
        'default'     => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => 0,
        ],
        'persistence' => [
            'host'     => env('REDIS_PERSISTENCE_HOST', '127.0.0.1'),
            'password' => env('REDIS_PERSISTENCE_PASSWORD', null),
            'port'     => env('REDIS_PERSISTENCE_PORT', 6380),
            'database' => 0,
        ],
        ...
    ],
    ...
]
```

## Tests
Tests for this library are written in PhpSpec.
We have the following test types:
- Unit testing, use `phpspec.yml` config file.
- Functional testing, use `phpspecIntrusive.yml` config file.