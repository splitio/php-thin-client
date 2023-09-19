<?php

namespace SplitIO\Test;

use SplitIO\ThinSdk\Manager;
use SplitIO\ThinSdk\SplitView;
use SplitIO\ThinSdk\Link\Consumer\Manager as LinkManager;
use Psr\Log\LoggerInterface;

use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{

    private $logger;

    public function setUp(): void
    {
        $this->logger = $this->createStub(LoggerInterface::class);
    }

    public function testSplitNamesOk()
    {
        $lm = $this->createMock(LinkManager::class);
        $lm->expects($this->once())->method('splitNames')
            ->with()
            ->willReturn(['s1', 's2']);

        $manager = new Manager($lm, $this->logger);
        $this->assertEquals(['s1', 's2'], $manager->splitNames());
    }

    public function testSplitNamesReturnsEmptyOnException()
    {
        $lm = $this->createMock(LinkManager::class);
        $lm->expects($this->once())->method('splitNames')
            ->will($this->throwException(new \Exception('sarasa')));

        $manager = new Manager($lm, $this->logger);
        $this->assertEquals([], $manager->splitNames());
    }

    public function testSplitOk()
    {
        $lm = $this->createMock(LinkManager::class);
        $lm->expects($this->once())->method('split')
            ->with('s1')
            ->willReturn(new SplitView('s1', 'tt', true, ['on', 'off'], 123, ['on' => 'frula']));

        $manager = new Manager($lm, $this->logger);
        $this->assertEquals(new SplitView('s1', 'tt', true, ['on', 'off'], 123, ['on' => 'frula']), $manager->split('s1'));
    }

    public function testSplitReturnNullOnException()
    {
        $lm = $this->createMock(LinkManager::class);
        $lm->expects($this->once())->method('split')
            ->with('s1')
            ->will($this->throwException(new \Exception('sarasa')));

        $manager = new Manager($lm, $this->logger);
        $this->assertEquals(null, $manager->split('s1'));
    }

    public function testSplitsOk()
    {
        $lm = $this->createMock(LinkManager::class);
        $lm->expects($this->once())->method('splits')
            ->with()
            ->willReturn([
                new SplitView('s1', 'tt', true, ['on', 'off'], 123, ['on' => 'frula']),
                new SplitView('s2', 'tt', false, ['on', 'off'], 124, ['on' => 'frula']),
            ]);

        $manager = new Manager($lm, $this->logger);
        $this->assertEquals([
            new SplitView('s1', 'tt', true, ['on', 'off'], 123, ['on' => 'frula']),
            new SplitView('s2', 'tt', false, ['on', 'off'], 124, ['on' => 'frula']),
        ], $manager->splits());
    }

    public function testSplitsReturnsEmptyOnException()
    {
        $lm = $this->createMock(LinkManager::class);
        $lm->expects($this->once())->method('splits')
            ->will($this->throwException(new \Exception('sarasa')));

        $manager = new Manager($lm, $this->logger);
        $this->assertEquals([], $manager->splits());
    }
}
