# Change log

## `2.0.2`
- Fixed `StorageProvider` segmentation fault, due to using class instead of interface, when binding to `StoreInterface`.

## `2.0.1`
- Requires `adore-me/common` `^2.0.5`, due to changes made in `findOneById` method, from repository.
- Added more automated tests for library. Functionality is not affected.
- Fixed issue with `PhpStaticVariableStore` that would allow to update by reference an object. Now the reference is "cut" when the object is stored, or provided. This makes the functionality more consistent with the other stores.
- `flush` function is now deprecated and will be removed in version `3.0.0`. Use `flushAll` or `flushNamespace` instead. Do note that `memcached` does not support `flushNamespace` and will throw exception when used.
- Fixed `TwoLevelCaching` `forgetByTags` that would not work correctly, when last 2 parameters were different than the default ones.

## `2.0.0`
- Updated composer dependency to allow `adore-me\common` version `^2.0.0`.
- Added more automated tests.
- Moved interfaces and models to new namespaces, for better visibility.
- Basic functionality was not changed. Backwards incompatibility because of moved models and interfaces.

## `1.0.0`
- Backwards incompatible with `0.0.3`.
- Made compatible with `Laravel 5.4`.
- Updated to use version `^1.0` of `adore-me/common` library.
- Removed dependency on laravel for `Stores`. Reason is that the `Laravel` `Illuminate\Contracts\Cache\Store` does not have proper type hinting, and has minutes instead of seconds for expiration. Also it was required for a better decoupling, so future `Laravel` updates will not break the functionality.
- This decoupling will also be done on `Cache\Repository` interfaces and models, at a future time.
- Moved all `Interfaces` and `Models` into new sub folders, `Cache` and `Store`. This means the namespaces were changed.
- Redis now supports both `predis` and `phpredis` connectors implemented by `Laravel 5.4`.
- The StorageHelper has two functions that helps you to instantiate redis and memcached, by giving configuration as parameters. These will yield Redis/Memcached stores.
- Made specIntrusive/spec tests for Redis to work outside of `Laravel` project.
  - During this, the spec was rewritten to support `phpredis` and `predis` connectors.
- Normalized code in `Store`, by using an abstract class. Removed a lot of useless spaces from code, or duplicate code, and moved into abstract.
- `EloquentRepositoryStoreTrait` renamed into `CacheRepositoryForEloquentRepositoryTrait`.
- `EloquentRepositoryStoreWithPriorityHandlerTrait` renamed into `CacheRepositoryForEloquentRepositoryWithPriorityHandlerTrait`.
- `EloquentRepositoryPhpStaticVariableStoreTrait` renamed into `PhpStaticVariableCacheRepositoryForEloquentRepositoryTrait`.
- `EloquentRepositoryPhpStaticVariableStoreWithPriorityHandlerTrait` renamed into `PhpStaticVariableCacheRepositoryForEloquentRepositoryWithPriorityHandlerTrait`.
- Renamed/Moved everything around. Too many to write here, let's just say all were renamed or moved.

## `0.0.3`
 - Backwards incompatible with `0.0.2`.
 - Created php static variable storage and traits for eloquent repository with store and with php static variable.
 - Fixed some code inconsistency.
 - Added new trait for store with priority handler.
   - `EloquentRepositoryStoreTrait`
   - `EloquentRepositoryStoreWithPriorityHandlerTrait`
   - `EloquentRepositoryPhpStaticVariableStoreTrait`
     - This is equivalent to `EloquentRepositoryStoreTrait`, but has a special handler for storing: does not transform the model, it saves at is it.
   - `EloquentRepositoryPhpStaticVariableStoreWithPriorityHandlerTrait`

## `0.0.2`
 - Backwards incompatible with `0.0.1`.
 - Created `AdoreMe\Storage\Traits\EloquentRepositoryCacheInStorageTrait`
 - Created `AdoreMe\Storage\Helpers\StorageHelper`
 - Fixed bug on `two level cache` not being able to do `forgetByTags`, because of different prefixes used by stores.

## `0.0.1`
- Initial commit
- Added models, helpers, interfaces, exceptions and traits that were common between Adore Me's laravel applications.
 - Interfaces:
   - `AdoreMe\Storage\Interfaces\MemcachedRepositoryInterface`
   - `AdoreMe\Storage\Interfaces\RedisRepositoryInterface`
   - `AdoreMe\Storage\Interfaces\RepositoryInterface`
   - `AdoreMe\Storage\Interfaces\StoreInterface`
   - `AdoreMe\Storage\Interfaces\TwoLevelCacheRepositoryInterface`
 - Models:
   - `AdoreMe\Storage\Models\MemcachedRepository`
   - `AdoreMe\Storage\Models\MemcachedStore`
   - `AdoreMe\Storage\Models\RedisRepository`
   - `AdoreMe\Storage\Models\RedisStore`
   - `AdoreMe\Storage\Models\Repository`
   - `AdoreMe\Storage\Models\TwoLevelCacheRepository`
   - `AdoreMe\Storage\Models\TwoLevelCacheStore`
 - Providers:
   - `AdoreMe\Storage\Providers\StorageProvider`
- For mode info about models, just read them :)