<?php
namespace AdoreMe\Storage\Providers;

use AdoreMe\Storage\Helpers\StorageHelper;
use AdoreMe\Storage\Interfaces\Repository\Cache\MemcachedCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\PhpStaticVariableCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\RedisCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\CacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Repository\Cache\TwoLevelCacheRepositoryInterface;
use AdoreMe\Storage\Interfaces\Store\MemcachedStoreInterface;
use AdoreMe\Storage\Interfaces\Store\PhpStaticVariableStoreInterface;
use AdoreMe\Storage\Interfaces\Store\RedisStoreInterface;
use AdoreMe\Storage\Interfaces\Store\StoreInterface;
use AdoreMe\Storage\Interfaces\Store\TwoLevelCacheStoreInterface;
use AdoreMe\Storage\Models\Repository\Cache\MemcachedCacheRepository;
use AdoreMe\Storage\Models\Repository\Cache\PhpStaticVariableCacheRepository;
use AdoreMe\Storage\Models\Repository\Cache\RedisCacheRepository;
use AdoreMe\Storage\Models\Repository\Cache\TwoLevelCacheRepository;
use AdoreMe\Storage\Models\Store\PhpStaticVariableStore;
use AdoreMe\Storage\Models\Store\TwoLevelCacheStore;
use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class StorageProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Memcached store.
        $this->app->singleton(
            MemcachedStoreInterface::class,
            function (Container $app) {
                return StorageHelper::makeMemcachedStoreFromConfiguration($app);
            }
        );
        // Redis store.
        $this->app->singleton(
            RedisStoreInterface::class,
            function (Container $app) {
                return StorageHelper::makeRedisStoreFromConfiguration($app);
            }
        );
        // Php static variable store.
        $this->app->singleton(
            PhpStaticVariableStoreInterface::class,
            function (Container $app) {
                $config = $app['config']['cache.stores.php_static_variable'];
                $prefix = $config['prefix'] ?? $app['config']['cache.prefix'];

                return new PhpStaticVariableStore(
                    $prefix
                );
            }
        );
        // Two level cache store.
        $this->app->singleton(
            TwoLevelCacheStoreInterface::class,
            function (Container $app) {
                $config = $app['config']['cache.stores.two_level_cache'];
                $prefix = $config['prefix'] ?? $app['config']['cache.prefix'];

                return new TwoLevelCacheStore(
                    StorageHelper::makeRedisStoreFromConfiguration($app, $prefix),
                    StorageHelper::makeMemcachedStoreFromConfiguration($app, $prefix),
                    $prefix
                );
            }
        );

        // Memcached repository.
        $this->app->singleton(
            MemcachedCacheRepositoryInterface::class,
            function (Container $app) {
                $repository = new MemcachedCacheRepository(
                    $app->make(MemcachedStoreInterface::class)
                );

                if ($app->bound(Dispatcher::class)) {
                    $repository->setEventDispatcher(
                        $app->make(Dispatcher::class)
                    );
                }

                return $repository;
            }
        );
        // Redis repository.
        $this->app->singleton(
            RedisCacheRepositoryInterface::class,
            function (Container $app) {
                $repository = new RedisCacheRepository(
                    $app->make(RedisStoreInterface::class)
                );

                if ($this->app->bound(Dispatcher::class)) {
                    $repository->setEventDispatcher(
                        $app[Dispatcher::class]
                    );
                }

                return $repository;
            }
        );
        // Php static variable repository.
        $this->app->singleton(
            PhpStaticVariableCacheRepositoryInterface::class,
            function (Container $app) {
                $repository = new PhpStaticVariableCacheRepository(
                    $app->make(PhpStaticVariableStoreInterface::class)
                );

                if ($this->app->bound(Dispatcher::class)) {
                    $repository->setEventDispatcher(
                        $app[Dispatcher::class]
                    );
                }

                return $repository;
            }
        );
        // Two level cache repository.
        $this->app->singleton(
            TwoLevelCacheRepositoryInterface::class,
            function (Container $app) {
                $repository = new TwoLevelCacheRepository(
                    $app->make(TwoLevelCacheStoreInterface::class)
                );

                if ($this->app->bound(Dispatcher::class)) {
                    $repository->setEventDispatcher(
                        $app[Dispatcher::class]
                    );
                }

                return $repository;
            }
        );

        // Bind two level cache store as default store interface.
        $this->app->singleton(
            StoreInterface::class,
            TwoLevelCacheStoreInterface::class
        );
        // Bind two level cache repository as default repository interface.
        $this->app->singleton(
            CacheRepositoryInterface::class,
            TwoLevelCacheRepositoryInterface::class
        );
    }

    /**
     * Registers the class name (~factory) into the IO Container
     *
     * @return array
     */
    public function provides()
    {
        return [
            // Stores.
            MemcachedStoreInterface::class,
            RedisStoreInterface::class,
            PhpStaticVariableStoreInterface::class,
            TwoLevelCacheStoreInterface::class,

            // Repositories.
            MemcachedCacheRepositoryInterface::class,
            RedisCacheRepositoryInterface::class,
            PhpStaticVariableCacheRepositoryInterface::class,
            TwoLevelCacheRepositoryInterface::class,

            // Store & Repository default bindings.
            StoreInterface::class,
            CacheRepositoryInterface::class,
        ];
    }
}
