<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Logging;
use SplitIO\ThinSdk\Config\Serialization;
use SplitIO\ThinSdk\Config\Main;
use SplitIO\ThinSdk\Config\Transfer;
use SplitIO\ThinSdk\Config\Utils;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use SplitIO\ThinSdk\Utils\ImpressionListener;

use PHPUnit\Framework\TestCase;

class MainTest extends TestCase
{

    public function testConfigDefault()
    {
        $cfg = Main::default();
        $this->assertEquals(Logging::default(), $cfg->logging());
        $this->assertEquals(Serialization::default(), $cfg->serialization());
        $this->assertEquals(Transfer::default(), $cfg->transfer());
        $this->assertEquals(Utils::default(), $cfg->utils());
    }

    public function testConfigParsing()
    {
        $logMock = $this->createMock(LoggerInterface::class);
        $ilMock = $this->createMock(ImpressionListener::class);

        $cfg = Main::fromArray([
            'transfer' => [
                'address' => 'someAddress',
                'type' => 'unix-stream',
                'timeout' => 123,
                'bufferSize' => 1234,
            ],
            'serialization' => [
                'mechanism' => 'msgpack',
            ],
            'logging' => [
                'psr-instance' => $logMock,
                'level' => LogLevel::DEBUG,
            ],
            'utils' => [
                'impressionListener' => $ilMock,
            ],
        ]);

        $this->assertEquals('someAddress', $cfg->transfer()->sockFN());
        $this->assertEquals('unix-stream', $cfg->transfer()->connType());
        $this->assertEquals(123, $cfg->transfer()->timeout());
        $this->assertEquals(1234, $cfg->transfer()->bufferSize());
        $this->assertEquals($logMock, $cfg->logging()->logger());
        $this->assertEquals('msgpack', $cfg->serialization()->mechanism());
        $this->assertEquals($ilMock, $cfg->utils()->impressionListener());
        $this->assertEquals(LogLevel::DEBUG, $cfg->logging()->level());
    }
}
