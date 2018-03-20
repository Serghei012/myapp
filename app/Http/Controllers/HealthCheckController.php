<?php

namespace AdoreMe\MsTest\Http\Controllers;

use AdoreMe\Logger\Traits\LoggerTrait;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController
{
    use LoggerTrait;

    /**
     * Check redis connection.
     *
     * @return JsonResponse
     */
    public function getRedisStatus(): JsonResponse
    {
        $connected = $this->checkRedisStatus();

        $response = response()->json(['Redis is up!'], 200);
        if (! $connected) {
            $response = response()->json(['Redis is down!'], 500);
        }

        return $response;
    }

    public function getStatus(): JsonResponse
    {
        $redis     = $this->checkRedisStatus();
        $memcached = $this->checkMemcacheStatus();
        $mysql     = $this->checkDbStatus();
        $status    = (($redis === true) && ($memcached === true) && ($mysql === true))
            ? 200 : 500;

        return response()->json(
            [
                'redis'    => $redis,
                'memcache' => $memcached,
                'mysql'    => $mysql
            ],
            $status
        );
    }

    /**
     * @return bool
     */
    protected function checkRedisStatus(): bool
    {
        $redis = new \Redis();
        try {
            $status = @$redis->connect(config('database.redis.default.host'));
            if ($status !== true) {
                return $status;
            }
            $time = (string) microtime();
            $key  = md5($time);
            $redis->set($key, $time);
            $get = $redis->get($key);
            $this->debug($get);

            return $time === $get;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return $e->getMessage();
        }
    }

    protected function checkMemcacheStatus(): bool
    {
        try {
            $time = (string) microtime();
            $key  = md5($time);
            /**
             * @var $cacheStore \Illuminate\Cache\MemcachedStore
             */
            $cacheStore = Cache::store('memcached');
            $cacheStore->add($key, $time, 10);
            $version = $cacheStore->getMemcached()->getVersion();
            $this->warning('Memcache version is ' . \GuzzleHttp\json_encode($version));
            $get = $cacheStore->get($key);
            $this->warning($time . '|' . $get);

            return ((string) $get) === $time;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return $e->getMessage();
        }
    }

    protected function checkDbStatus(): bool
    {
        try {
            DB::table('test')->get();

            return true;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return $e->getMessage();
        }
    }

    public function logTest()
    {
        $this->debug('Memcache test log entry - debug');
        $this->warning('Memcache test log entry - warning');
        $this->error('Memcache test log entry - error');
        $this->critical('Memcache test log entry - critical');

        return response()->json(['logged stuff']);
    }
}
