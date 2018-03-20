<?php
namespace benchmark\AdoreMe\Storage\Models\Store;

use AdoreMe\Common\Helpers\ArrayHelper;
use AdoreMe\Storage\Models\Store\RedisStore;
use Illuminate\Redis\RedisManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require __DIR__.'/../../../../vendor/autoload.php';

class RedisBenchmark
{
    const TAGS_PER_KEY = 5;
    const KEYS_COUNT   = 10000;

    /** @var OutputInterface */
    protected $output;

    /** @var RedisStore */
    protected $store;

    /** @var string */
    protected $fillData;

    /**
     * Create a new command instance.
     *
     * @param string $client
     * @param string $fillData
     */
    public function __construct(string $client, string $fillData)
    {
        $this->output   = new ConsoleOutput();
        $this->store    = $this->constructRedisWithClient($client);
        $this->fillData = $fillData;

        $this->run();
    }

    /**
     * Construct the redis store.
     *
     * @var $this RedisStore
     * @param string $client
     * @return RedisStore
     */
    protected function constructRedisWithClient(string $client): RedisStore
    {
        $this->info('Building Redis with client: ' . $client);

        /** @var RedisStore $this */
        $config = [
            'default' => [
                'host'     => 'spec.redis',
                'port'     => '6379',
                'database' => 0
            ]
        ];

        return new RedisStore(
            (new RedisManager($client, $config))->connection(),
            'spec'
        );
    }

    /**
     * Run the benchmark.
     *
     * @return void
     */
    protected function run()
    {
        $this->info('Running writing benchmark testing...');
        $this->benchmarkWrite();

        $this->benchmarkRead();

        $this->line('-----------------------------------');
        $this->store->flushAll();
    }

    /**
     * Benchmark the write speed.
     */
    protected function benchmarkWrite()
    {
        $this->info('Generating ' . self::KEYS_COUNT . ' random keys');
        $start = microtime(true);

        // Adding random data.
        $progressBar = $this->createProgressBar(self::KEYS_COUNT);
        for ($keyCount = 1 ; $keyCount <= self::KEYS_COUNT ; $keyCount++) {
            $this->store->put(
                'test_key' . $keyCount,
                $this->fillData,
                1,
                ArrayHelper::generateRandomArray(self::TAGS_PER_KEY, 1, 1, 0, 10, false)
            );

            $progressBar->advance();
        }
        $progressBar->finish();
        $this->line('');
        $this->info('Done in '. number_format(microtime(true) - $start, 2) . '(s)');
    }

    /**
     * Benchmark the read speed.
     */
    protected function benchmarkRead()
    {
        $this->info('Reading ' . self::KEYS_COUNT . ' keys');
        $start = microtime(true);

        $progressBar = $this->createProgressBar(self::KEYS_COUNT);
        for ($keyCount = 1 ; $keyCount <= self::KEYS_COUNT ; $keyCount++) {
            $this->store->get('test_key' . $keyCount);

            $progressBar->advance();
        }
        $progressBar->finish();
        $this->line('');
        $this->info('Done in '. number_format(microtime(true) - $start, 2) . '(s)');
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @return void
     */
    protected function info($string)
    {
        $this->line($string, 'info');
    }

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string  $style
     * @return void
     */
    protected function line($string, $style = null)
    {
        $styled = $style ? "<$style>$string</$style>" : $string;

        $this->output->writeln($styled);
    }

    /**
     * @param int $max
     *
     * @return ProgressBar
     */
    protected function createProgressBar($max = 0)
    {
        return new ProgressBar($this->output, $max);
    }
}

$filLData = json_encode(ArrayHelper::generateRandomArray(10, 2, 2));
new RedisBenchmark('predis', $filLData);
new RedisBenchmark('phpredis', $filLData);