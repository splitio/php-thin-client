<?php

namespace SplitIO\Test\Link\Transfer;

use SplitIO\ThinClient\Link\Transfer\UnixPacket;
use SplitIO\ThinClient\Link\Transfer\ConnectionException;
use SplitIO\Test\Utils\SocketServerRemoteControl;

use PHPUnit\Framework\TestCase;

class UnixSeqPacketTest extends TestCase
{
    private $socketServerRC;

    public function setUp(): void
    {
        if (php_uname('s') != 'Linux') {
            $this->markTestSkipped('Unix/SEQPACKET tests can only run on GNU/Linux');
            return;
        }

        if (getenv("DEBUG") == true) {
            fwrite(STDERR, "preparing socket server for test `" . $this->getName() . "`\n");
        }
        $this->socketServerRC = new SocketServerRemoteControl();
    }

    public function testHappyExchange(): void
    {

        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_SEQPACKET, $serverAddress, 1, [
            [
                'expects' => 'something',
                'returns' => 'something else',
            ],
            [
                'expects' => 'another interaction',
                'returns' => 'another interaction response',
            ],
        ]);

        $this->socketServerRC->awaitServerReady();

        $realSock = new UnixPacket($serverAddress);

        $realSock->sendMessage("something");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "something else");

        $realSock->sendMessage("another interaction");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "another interaction response");

        $this->socketServerRC->awaitDone(2);
    }

    public function testDeadSocket(): void
    {
        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";

        $this->expectExceptionObject(new ConnectionException("failed to connect to remote socket $serverAddress: Connection refused"));

        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_SEQPACKET, $serverAddress, 0, []);
        $this->socketServerRC->awaitServerReady();
        $this->socketServerRC->awaitFinished();

        new UnixPacket($serverAddress);
    }

    public function testNoDaemonRunning(): void
    {
        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";
        $tamperedAddress = $serverAddress . "someExtraChars";

        $this->expectExceptionObject(new ConnectionException("failed to connect to remote socket $tamperedAddress: No such file or directory"));
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_SEQPACKET, $serverAddress, 0, []);
        $this->socketServerRC->awaitServerReady();
        $this->socketServerRC->awaitFinished();

        new UnixPacket($tamperedAddress);
    }

    public function testConnectionBreaksBefore2ndInteraction(): void
    {
        $this->expectExceptionObject(new ConnectionException("error writing to socket: Broken pipe"));

        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_SEQPACKET, $serverAddress, 1, [
            [
                'expects' => 'something',
                'returns' => 'something else',
            ],
            [
                'actions_pre' => ['type' => 'break'],
            ],
        ]);

        $this->socketServerRC->awaitServerReady();

        $realSock = new UnixPacket($serverAddress);
        $realSock->sendMessage("something");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "something else");

        $this->socketServerRC->awaitDone(1);
        $this->socketServerRC->awaitFinished();

        $realSock->sendMessage("another interaction");
        $realSock->readMessage();

        $this->fail("should not get here");
    }

    public function testReadTimeout(): void
    {
        $this->expectExceptionObject(new ConnectionException("error reading from socket: Resource temporarily unavailable"));

        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_SEQPACKET, $serverAddress, 1, [
            [
                'expects' => 'something',
                'returns' => 'something else',
            ],
            [
                'expects' => 'something as well',
                'actions_during' => [
                    'type' => 'delay',
                    'us' => 2000000
                ],
            ],
        ]);

        $this->socketServerRC->awaitServerReady();

        $realSock = new UnixPacket($serverAddress, ['timeout' => ['sec' => 1, 'usec' => 0]]);
        $realSock->sendMessage("something");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "something else");

        $this->socketServerRC->awaitDone(1);

        $realSock->sendMessage('something as well');
        $realSock->readMessage();
        $this->socketServerRC->awaitDone(2);
    }

    public function testLargePayloads(): void
    {
        if (getenv("GHA") == "true") {
            $this->markTestSkipped("test cannot currently be run in GHA");
            return;
        }

        $payloadToSend = str_repeat('qwer', 1000000); // ~4mb
        $paylaodToReceive = str_repeat('asdf', 1000000); // ~4mb

        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_SEQPACKET, $serverAddress, 1, [
            [
                'expects' => $payloadToSend,
                'returns' => $paylaodToReceive,
            ],
        ]);

        $this->socketServerRC->awaitServerReady();

        $realSock = new UnixPacket($serverAddress, [
            'sendBufferSize' => 4000000,
            'recvBufferSize' => 4000000,
        ]);
        $realSock->sendMessage($payloadToSend);
        $response = $realSock->readMessage();
        $this->assertEquals($response, $paylaodToReceive);

        $this->socketServerRC->awaitDone(1);
    }

    public function tearDown(): void
    {
        $this->socketServerRC->shutdown();
    }
}
