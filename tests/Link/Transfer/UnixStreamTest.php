<?php

namespace SplitIO\Test\Link\Transfer;

use SplitIO\ThinClient\Link\Transfer\UnixStream;
use SplitIO\ThinClient\Link\Transfer\ConnectionException;
use SplitIO\Test\Utils\SocketServerRemoteControl;

use PHPUnit\Framework\TestCase;

class UnixStreamTest extends TestCase
{
    private $socketServerRC;

    public function setUp(): void
    {
        fwrite(STDERR, "CREATING SOCKET SERVER RC\n");
        $this->socketServerRC = new SocketServerRemoteControl();
        fwrite(STDERR, "CREATED SOCKET SERVER RC\n");
    }

    public function testHappyExchange(): void
    {
        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests.sock";
        fwrite(STDERR, "STARTING SOCKET SERVER\n");
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 1, [
            [
                'expects' => 'something',
                'returns' => 'something else',
            ],
            [
                'expects' => 'another interaction',
                'returns' => 'another interaction response',
            ],
        ]);
        fwrite(STDERR, "STARTED SOCKET SERVER\n");

        $this->socketServerRC->awaitServerReady();

        fwrite(STDERR, "SOCKET SERVER READY\n");

        $realSock = new UnixStream($serverAddress);

        $realSock->sendMessage("something");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "something else");

        $realSock->sendMessage("another interaction");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "another interaction response");


        fwrite(STDERR, "AWAITIND DONE(2)\n");

        $this->socketServerRC->awaitDone(2);

        fwrite(STDERR, "AWAITING DONE(2) OK\n");

    }

    public function testDeadSocket(): void
    {
        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";

        $this->expectExceptionObject(new ConnectionException("failed to connect to remote socket $serverAddress: Connection refused"));

        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 0, []);
        $this->socketServerRC->awaitServerReady();
        $this->socketServerRC->awaitFinished();

        new UnixStream($serverAddress);
    }

    public function testNoDaemonRunning(): void
    {
        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";
        $tamperedAddress = $serverAddress . "someExtraChars";

        $this->expectExceptionObject(new ConnectionException("failed to connect to remote socket $tamperedAddress: No such file or directory"));
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 0, []);
        $this->socketServerRC->awaitServerReady();
        $this->socketServerRC->awaitFinished();

        new UnixStream($tamperedAddress);
    }

    public function testConnectionBreaksBefore2ndInteraction(): void
    {
        $this->expectException(ConnectionException::class);

        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests.sock";
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 1, [
            [
                'expects' => 'something',
                'returns' => 'something else',
            ],
            [
                'actions_pre' => ['type' => 'break'],
            ],
        ]);

        $this->socketServerRC->awaitServerReady();

        $realSock = new UnixStream($serverAddress);
        $realSock->sendMessage("something");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "something else");

        $this->socketServerRC->awaitDone(1);

        $realSock->sendMessage("another interaction");
        $realSock->readMessage();

        $this->fail("should not get here");
    }

    public function testReadTimeout(): void
    {
        $this->expectExceptionObject(new ConnectionException("error reading from socket: Resource temporarily unavailable"));

        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests.sock";
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 1, [
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

        $realSock = new UnixStream($serverAddress, ['timeout' => ['sec' => 1, 'usec' => 0]]);
        $realSock->sendMessage("something");
        $response = $realSock->readMessage();
        $this->assertEquals($response, "something else");

        $this->socketServerRC->awaitDone(1);

        $realSock->sendMessage('something as well');
        $realSock->readMessage();
    }

    public function testLargePayloads(): void
    {
        $payloadToSend = str_repeat('qwertyui', 1000000); // ~8mb
        $paylaodToReceive = str_repeat('asdfghjk', 1000000); // ~8mb

        $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests.sock";
        $this->socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 1, [
            [
                'expects' => $payloadToSend,
                'returns' => $paylaodToReceive,
            ],
        ]);

        $this->socketServerRC->awaitServerReady();

        $realSock = new UnixStream($serverAddress);
        $realSock->sendMessage($payloadToSend);
        $response = $realSock->readMessage();
        $this->assertEquals($response, $paylaodToReceive);

        $this->socketServerRC->awaitDone(1);
    }

    public function tearDown(): void
    {
        fwrite(STDERR, "SHUTTING DOWN\n");
        $this->socketServerRC->shutdown();
        fwrite(STDERR, "SHUTTING DOWN OK\n");
    }
}
