<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Config\Utils;
use SplitIO\ThinSdk\Utils\EvalCache;
use SplitIO\ThinSdk\Utils\EvalCache\Helpers;
// use SplitIO\ThinSdk\Utils\EvalCache\InputHasher;

use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{

    public function testCacheBuildingNoCache()
    {

        $logger = $this->createMock(LoggerInterface::class);
        $c = Helpers::getCache(Utils::fromArray([
            'evaluationCache' => 'none',
            'customCacheHash' => null,
            'cacheEvictionPolicy' => null,
            'cacheMaxSize' => null,
        ]), $logger);

        $this->assertEquals(new EvalCache\NoCache(), $c);
    }

    public function testCacheBuildingNoEviction()
    {

        $logger = $this->createMock(LoggerInterface::class);
        $c = Helpers::getCache(Utils::fromArray([
            'evaluationCache' => 'key-only',
            'customCacheHash' => null,
            'cacheEvictionPolicy' => 'none',
            'cacheMaxSize' => 1001,
        ]), $logger);

        $this->assertEquals(new EvalCache\CacheImpl(new EvalCache\KeyOnlyHasher(), new EvalCache\NoEviction(1001)), $c);
    }

    public function testCacheBuildingRandomEviction()
    {

        $logger = $this->createMock(LoggerInterface::class);
        $c = Helpers::getCache(Utils::fromArray([
            'evaluationCache' => 'key-only',
            'customCacheHash' => null,
            'cacheEvictionPolicy' => 'random',
            'cacheMaxSize' => 1000,
        ]), $logger);

        $this->assertEquals(new EvalCache\CacheImpl(new EvalCache\KeyOnlyHasher(), new EvalCache\RandomEviction(1000)), $c);
    }
}
