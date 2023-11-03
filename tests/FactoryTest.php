<?php

namespace SplitIO\Test;

use \SplitIO\ThinSdk\Factory;
use \PHPUnit\Framework\TestCase;

use SplitIO\ThinSdk\ClientInterface;
use SplitIO\ThinSdk\ManagerInterface;
use SplitIO\ThinSdk\Fallback\AlwaysControlClient;
use SplitIO\ThinSdk\Fallback\AlwaysEmptyManager;
use SplitIO\ThinSdk\Link\Serialization\MessagePack\DateTimeExtension;
use SplitIO\ThinSdk\Link\Protocol\V1\RPC;
use SplitIO\ThinSdk\Link\Protocol\V1\RegisterFlags;

use SplitIO\Test\TestUtils\SocketServerRemoteControl;

use MessagePack\Packer;
use MessagePack\Extension\TimestampExtension;


class FactoryTest extends TestCase
{

    public function testGetFactoryOk()
    {
        // This test will create a socket server, attempt to register, and validate that the correct
        // client & manager have been created.

        $packer = (new Packer())->extendWith(new TimestampExtension(), new DateTimeExtension());

        $socketServerRC = new SocketServerRemoteControl();

        try {
            $serverAddress = sys_get_temp_dir() . "/php_thin_client_tests_seqpacket.sock";
            $socketServerRC->start(SocketServerRemoteControl::UNIX_STREAM, $serverAddress, 1, [
                [
                    'expects' => $packer->pack(RPC::forRegister("someId", new RegisterFlags(false))->getSerializable()),
                    'returns' => $packer->pack(['s' => 0x01]),
                ],
            ]);

            $socketServerRC->awaitServerReady();

            $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
            $factory = Factory::withConfig([
                'transfer' => [
                    'address' => $serverAddress,
                    'type' => 'unix-stream',
                ],
                'logging' => ['psr-instance' => $logger],
            ]);

            $this->assertEquals('SplitIO\ThinSdk\Client', get_class($factory->client()));
        } finally {
            $socketServerRC->shutdown();
        }
    }

    public function testGetFactoryLinkErrorWithFallback()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $factory = Factory::withConfig([
            'transfer' => [
                'address' => '/non/existant/file',
                'type' => 'unix-stream',
            ],
            'logging' => ['psr-instance' => $logger],
        ]);
        $this->assertEquals(new AlwaysControlClient(), $factory->client());
        $this->assertEquals(new AlwaysEmptyManager(), $factory->manager());
    }

    public function testGetFactoryLinkErrorNoFallback()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->expectExceptionMessage('failed to connect to remote socket /non/existant/file: No such file or directory');
        Factory::withConfig([
            'transfer' => [
                'address' => '/non/existant/file',
                'type' => 'unix-stream',
            ],
            'logging' => ['psr-instance' => $logger],
            'fallback' => ['disable' => true],
        ]);
    }

    public function testGetFactoryLinkErrorCustomFallback()
    {
        $client = $this->createMock(ClientInterface::class);
        $manager = $this->createMock(ManagerInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $factory = Factory::withConfig([
            'transfer' => [
                'address' => '/non/existant/file',
                'type' => 'unix-stream',
            ],
            'logging' => ['psr-instance' => $logger],
            'fallback' => ['client' => $client, 'manager' => $manager],
        ]);
        $this->assertEquals($client, $factory->client());
        $this->assertEquals($manager, $factory->manager());
    }
}
