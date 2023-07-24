<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Logging;
use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;

use PHPUnit\Framework\TestCase;

class LoggingTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Logging::default();
        $this->assertEquals(null, $cfg->logger());
        $this->assertEquals(LogLevel::INFO, $cfg->level());
    }

    public function testConfigParsing()
    {
        $logMock = $this->createMock(LoggerInterface::class);
        $cfg = Logging::fromArray([
            'psr-instance' => $logMock,
            'level' => LogLevel::DEBUG,
        ]);

        $this->assertEquals($logMock, $cfg->logger());
        $this->assertEquals(LogLevel::DEBUG, $cfg->level());
    }

}
