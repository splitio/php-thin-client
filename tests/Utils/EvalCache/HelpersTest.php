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
            'cacheMaxSize' => 1000,
        ]), $logger);

        $this->assertEquals(new EvalCache\CacheImpl(new EvalCache\KeyOnlyHasher(), new EvalCache\NoEviction()), $c);
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

    public function testCacheBuildingMostRecentEviction()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $c = Helpers::getCache(Utils::fromArray([
            'evaluationCache' => 'key-attributes',
            'customCacheHash' => null,
            'cacheEvictionPolicy' => 'most-recent',
            'cacheMaxSize' => 1000,
        ]), $logger);

        $this->assertEquals(new EvalCache\CacheImpl(new EvalCache\KeyAttributeCRC32Hasher(), new EvalCache\MostRecentEviction(1000)), $c);
    }

    public function testCacheBuildingCustomInputHash()
    {
        $ihMock = $this->createMock(EvalCache\InputHasher::class);
        $logger = $this->createMock(LoggerInterface::class);
        $c = Helpers::getCache(Utils::fromArray([
            'evaluationCache' => 'custom',
            'customCacheHash' => $ihMock,
            'cacheEvictionPolicy' => 'most-recent',
            'cacheMaxSize' => 1000,
        ]), $logger);

        $this->assertEquals(new EvalCache\CacheImpl($ihMock, new EvalCache\MostRecentEviction(1000)), $c);
    }
}
