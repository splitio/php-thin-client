<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Config;
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
        $c = Helpers::getCache(Config\EvaluationCache::fromArray([
            'typr' => 'none',
            'customHash' => null,
            'evictionPolicy' => null,
            'maxSize' => null,
        ]), $logger);

        $this->assertEquals(new EvalCache\NoCache(), $c);
    }

    public function testCacheBuildingNoEviction()
    {

        $logger = $this->createMock(LoggerInterface::class);
        $c = Helpers::getCache(Config\EvaluationCache::fromArray([
            'type' => 'key-only',
            'customHash' => null,
            'evictionPolicy' => 'none',
            'maxSize' => 1001,
        ]), $logger);

        $this->assertEquals(new EvalCache\CacheImpl(new EvalCache\KeyOnlyHasher(), new EvalCache\NoEviction(1001)), $c);
    }

    public function testCacheBuildingRandomEviction()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $c = Helpers::getCache(Config\EvaluationCache::fromArray([
            'type' => 'key-only',
            'customHash' => null,
            'evictionPolicy' => 'random',
            'maxSize' => 1000,
        ]), $logger);

        $this->assertEquals(new EvalCache\CacheImpl(new EvalCache\KeyOnlyHasher(), new EvalCache\RandomEviction(1000)), $c);
    }
}
