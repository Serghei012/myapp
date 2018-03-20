<?php
namespace specIntrusive\AdoreMe\Storage\Models\Store;

use AdoreMe\Storage\Models\Store\RedisStore;

class RedisStoreTestWithPhpRedisSpec extends RedisStoreSpec
{
    function let()
    {
        $this->beAnInstanceOf(RedisStore::class);

        $this->beConstructedWithClient('phpredis');
    }
}
