<?php
namespace laravel\AdoreMe\Library\Fixtures\Providers;

use Illuminate\Support\ServiceProvider;
use laravel\AdoreMe\Library\Fixtures\Interfaces\ProductInterface;
use laravel\AdoreMe\Library\Fixtures\Interfaces\ProductRepositoryInterface;
use laravel\AdoreMe\Library\Fixtures\Models\Product;
use laravel\AdoreMe\Library\Fixtures\Models\ProductRepository;

class RepositoryProvider extends ServiceProvider
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
        $this->app->singleton(ProductInterface::class, Product::class);
        $this->app->singleton(ProductRepositoryInterface::class, ProductRepository::class);
    }

    /**
     * Registers the class name (~factory) into the IO Container
     *
     * @return array
     */
    public function provides()
    {
        return [
            ProductInterface::class,
            ProductRepositoryInterface::class,
        ];
    }
}
