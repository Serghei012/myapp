<?php
namespace stubs\AdoreMe\Common\Application;

use Closure;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Monolog\Logger;
use Symfony\Component\Console\Input\ArrayInput as ConsoleArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

if (! defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

/**
 * How to use it:
 *
 * You will need to require_once the composer auto loader: `require_once __DIR__ . '/../vendor/autoload.php';`
 *
 * Also you will need to define some variable:
 *
 * $basePath          = realpath(__DIR__ . '/../');
 * $httpKernel        = laravel\AdoreMe\Common\Http\Kernel::class;
 * $consoleKernel     = laravel\AdoreMe\Common\Console\Kernel::class;
 * $exceptionsHandler = laravel\AdoreMe\Common\Exceptions\Handler::class;
 *
 * setUp() and setDown() to prepare and unprepare laravel application (db, cache, storage, etc)
 *
 * You can add closure executions to setUp and setDown by using
 * addSetUpOperation(Closure) and addSetDownOperation(Closure).
 */
class Laravel extends Application
{
    /** @var array */
    protected $setUpOperations = [];

    /** @var array */
    protected $setDownOperations = [];

    /**
     * Create the application.
     *
     * @param string $basePath
     * @param string $httpKernel
     * @param string $consoleKernel
     * @param string $exceptionsHandler
     */
    public function __construct(string $basePath, string $httpKernel, string $consoleKernel, string $exceptionsHandler)
    {
        parent::__construct($basePath);

        // Instantiate a different .env file.
        $this->loadEnvironmentFrom('.env.testing');

        // Bind Important Interfaces
        /** @noinspection PhpUndefinedClassInspection */
        $this->singleton(
            HttpKernel::class,
            $httpKernel
        );

        /** @noinspection PhpUndefinedClassInspection */
        $this->singleton(
            ConsoleKernel::class,
            $consoleKernel
        );

        /** @noinspection PhpUndefinedClassInspection */
        $this->singleton(
            ExceptionHandler::class,
            $exceptionsHandler
        );

        // Do not instantiate monolog.
        $this->configureMonologUsing(
            function (Logger $monologModel) {
            }
        );

        $this->setUp();
    }

    /**
     * Bind an execution, at end of application.
     */
    public function __destruct()
    {
        $this->setDown();
    }

    /**
     * Prepare Laravel for testing, by running the migrations, etc.
     */
    public function setUp()
    {
        $kernel = $this->make(ConsoleKernel::class);
        $input  = new ConsoleArrayInput(['command' => 'migrate:refresh']);
        $status = $kernel->handle($input, new ConsoleOutput(ConsoleOutput::VERBOSITY_QUIET));
        $kernel->terminate($input, $status);

        foreach ($this->setUpOperations as $closure) {
            $closure($this);
        }
    }

    /**
     * Unprepare Laravel, by resetting the migrations, dropping table "migrations", flushing caches, storage, etc.
     */
    public function setDown()
    {
        $kernel = $this->make(ConsoleKernel::class);
        $input  = new ConsoleArrayInput(['command' => 'migrate:reset']);
        $status = $kernel->handle($input, new ConsoleOutput(ConsoleOutput::VERBOSITY_QUIET));
        $kernel->terminate($input, $status);

        /** @var \Illuminate\Support\Facades\Schema $schema */
        $schema = $this->make('Schema');
        /** @noinspection PhpUndefinedMethodInspection */
        $schema::dropIfExists('migrations');

        foreach ($this->setDownOperations as $closure) {
            $closure($this);
        }
    }

    /**
     * Add a closure to be executed when application is set up.
     *
     * @param Closure $closure
     */
    public function addSetUpOperation(Closure $closure)
    {
        $this->setUpOperations[] = $closure;
    }

    /**
     * Add a closure to be executed when application is set down.
     *
     * @param Closure $closure
     */
    public function addSetDownOperation(Closure $closure)
    {
        $this->setDownOperations[] = $closure;
    }
}
