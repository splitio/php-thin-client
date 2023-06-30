<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinClient\Config\Utils;
use SplitIO\ThinClient\Utils\ImpressionListener;
use SplitIO\ThinClient\Link\Protocol\V1\RPC;
use SplitIO\ThinClient\Link\Protocol\V1\RegisterFlags;
use SplitIO\ThinClient\Link\Protocol\V1\ImpressionListenerData;
use SplitIO\ThinClient\Link\Consumer\V1Manager;
use SplitIO\ThinClient\Link\Transfer\ConnectionFactory;
use SplitIO\ThinClient\Link\Transfer\RawConnection;
use SplitIO\ThinClient\Link\Transfer\ConnectionException;
use SplitIO\ThinClient\Link\Serialization\SerializerFactory;
use SplitIO\ThinClient\Link\Serialization\Serializer;

use PHPUnit\Framework\TestCase;

class V1ManagerTest extends TestCase
{

    private $logger;

    public function setUp(): void
    {
        $this->logger = $this->createStub(\Psr\Log\LoggerInterface::class);
    }

    public function testHappyExchangeNoImpListener(): void
    {
        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->at(0))->method('sendMessage')->with('serializedRegister');
        $connMock->expects($this->at(1))->method('readMessage')->willReturn('serializedRegisterResp');
        $connMock->expects($this->at(2))->method('sendMessage')->with('serializedTreatment');
        $connMock->expects($this->at(3))->method('readMessage')->willReturn('serializedTreatmentResp');
        $connMock->expects($this->at(4))->method('sendMessage')->with('serializedTreatments');
        $connMock->expects($this->at(5))->method('readMessage')->willReturn('serializedTreatmentsResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->at(0))->method('serialize')->with(RPC::forRegister('someId', new RegisterFlags(false)))
            ->willReturn('serializedRegister');
        $serializerMock->expects($this->at(1))->method('deserialize')->with('serializedRegisterResp')
            ->willReturn(['s' => 0x01]);
        $serializerMock->expects($this->at(2))->method('serialize')->with(RPC::forTreatment("k", "b", "f", ["a" => 1]))
            ->willReturn('serializedTreatment');
        $serializerMock->expects($this->at(3))->method('deserialize')->with('serializedTreatmentResp')
            ->willReturn(['s' => 0x01, 'p' => ['t' => 'on']]);
        $serializerMock->expects($this->at(4))->method('serialize')->with(RPC::forTreatments("k", "b", ["f1", "f2", "f3"], ["a" => 1]))
            ->willReturn('serializedTreatments');
        $serializerMock->expects($this->at(5))->method('deserialize')->with('serializedTreatmentsResp')
            ->willReturn(['s' => 0x01, 'p' => ['r' => [['t' => 'on'], ['t' => 'on'], ['t' => 'off']]]]);


        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(
            ['on', null, null],
            $v1Manager->getTreatment("k", "b", "f", ["a" => 1])
        );
        $this->assertEquals(
            ['f1' => ['on', null, null], 'f2' => ['on', null, null], 'f3' => ['off', null, null]],
            $v1Manager->getTreatments('k', 'b', ['f1', 'f2', 'f3'], ['a' => 1])
        );
    }

    public function testHappyExchangeWithImpListener(): void
    {
        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->at(0))->method('sendMessage')->with('serializedRegister');
        $connMock->expects($this->at(1))->method('readMessage')->willReturn('serializedRegisterResp');
        $connMock->expects($this->at(2))->method('sendMessage')->with('serializedTreatment');
        $connMock->expects($this->at(3))->method('readMessage')->willReturn('serializedTreatmentResp');
        $connMock->expects($this->at(4))->method('sendMessage')->with('serializedTreatments');
        $connMock->expects($this->at(5))->method('readMessage')->willReturn('serializedTreatmentsResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->at(0))->method('serialize')->with(RPC::forRegister('someId', new RegisterFlags(true)))
            ->willReturn('serializedRegister');
        $serializerMock->expects($this->at(1))->method('deserialize')->with('serializedRegisterResp')
            ->willReturn(['s' => 0x01]);
        $serializerMock->expects($this->at(2))->method('serialize')->with(RPC::forTreatment("k", "b", "f", ["a" => 1]))
            ->willReturn('serializedTreatment');
        $serializerMock->expects($this->at(3))->method('deserialize')->with('serializedTreatmentResp')
            ->willReturn(['s' => 0x01, 'p' => ['t' => 'on', 'l' => ['l' => 'lab1', 'c' => 123, 'm' => 1234]]]);
        $serializerMock->expects($this->at(4))->method('serialize')->with(RPC::forTreatments("k", "b", ["f1", "f2", "f3"], ["a" => 1]))
            ->willReturn('serializedTreatments');
        $serializerMock->expects($this->at(5))->method('deserialize')->with('serializedTreatmentsResp')
            ->willReturn(['s' => 0x01, 'p' => ['r' => [
                ['t' => 'on', 'l' => ['l' => 'lab1', 'c' => 123, 'm' => 1234]],
                ['t' => 'on', 'l' => ['l' => 'lab2', 'c' => 124, 'm' => 1235]],
                ['t' => 'off', 'l' => ['l' => 'lab3', 'c' => 125, 'm' => 1236]],
            ]]]);

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $ilMock = $this->createMock(ImpressionListener::class);
        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::fromArray(['impressionListener' => $ilMock]), $this->logger);
        $this->assertEquals(
            ['on', new ImpressionListenerData('lab1', 123, 1234), null],
            $v1Manager->getTreatment("k", "b", "f", ["a" => 1])
        );
        $this->assertEquals(
            [
                'f1' => ['on', new ImpressionListenerData('lab1', 123, 1234), null],
                'f2' => ['on', new ImpressionListenerData('lab2', 124, 1235), null],
                'f3' => ['off', new ImpressionListenerData('lab3', 125, 1236), null],
            ],
            $v1Manager->getTreatments('k', 'b', ['f1', 'f2', 'f3'], ['a' => 1])
        );
    }

    public function testRegisterFailCrashes(): void
    {
        $this->expectException(ConnectionException::class);

        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->at(0))->method('sendMessage')->with('serializedRegister')->will($this->throwException(new ConnectionException("some")));

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->at(0))->method('serialize')->with(RPC::forRegister('someId', new RegisterFlags(false)))
            ->willReturn('serializedRegister');

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $v1Manager->getTreatment("k", "b", "f", ["a" => 1]);
    }

    public function testPostRegisterRPCsAreRetried(): void
    {
        $connMock1 = $this->createMock(RawConnection::class);
        $connMock1->expects($this->at(0))->method('sendMessage')->with('serializedRegister');
        $connMock1->expects($this->at(1))->method('readMessage')->willReturn('serializedRegisterResp');
        $connMock1->expects($this->at(2))->method('sendMessage')->with('serializedTreatment')->will($this->throwException(new ConnectionException("a")));

        $connMock2 = $this->createMock(RawConnection::class);
        $connMock2->expects($this->at(0))->method('sendMessage')->with('serializedRegister');
        $connMock2->expects($this->at(1))->method('readMessage')->willReturn('serializedRegisterResp');
        $connMock2->expects($this->at(2))->method('sendMessage')->with('serializedTreatment');
        $connMock2->expects($this->at(3))->method('readMessage')->willReturn('serializedTreatmentResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->at(0))->method('create')->willReturn($connMock1);
        $connFactoryMock->expects($this->at(1))->method('create')->willReturn($connMock2);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->at(0))->method('serialize')->with(RPC::forRegister('someId', new RegisterFlags(false)))
            ->willReturn('serializedRegister');
        $serializerMock->expects($this->at(1))->method('deserialize')->with('serializedRegisterResp')
            ->willReturn(['s' => 0x01]);
        $serializerMock->expects($this->at(2))->method('serialize')->with(RPC::forTreatment("k", "b", "f", ["a" => 1]))
            ->willReturn('serializedTreatment');
        $serializerMock->expects($this->at(3))->method('serialize')->with(RPC::forRegister('someId', new RegisterFlags(false)))
            ->willReturn('serializedRegister');
        $serializerMock->expects($this->at(4))->method('deserialize')->with('serializedRegisterResp')
            ->willReturn(['s' => 0x01]);
        $serializerMock->expects($this->at(5))->method('serialize')->with(RPC::forTreatment("k", "b", "f", ["a" => 1]))
            ->willReturn('serializedTreatment');
        $serializerMock->expects($this->at(6))->method('deserialize')->with('serializedTreatmentResp')
            ->willReturn(['s' => 0x01, 'p' => ['t' => 'on']]);


        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(
            ['on', null, null],
            $v1Manager->getTreatment("k", "b", "f", ["a" => 1])
        );
    }

    public function test2FailuresCrash(): void
    {
        $this->expectException(ConnectionException::class);

        $connMock1 = $this->createMock(RawConnection::class);
        $connMock1->expects($this->at(0))->method('sendMessage')->with('serializedRegister');
        $connMock1->expects($this->at(1))->method('readMessage')->willReturn('serializedRegisterResp');
        $connMock1->expects($this->at(2))->method('sendMessage')->with('serializedTreatment')->will($this->throwException(new ConnectionException("a")));

        $connMock2 = $this->createMock(RawConnection::class);
        $connMock2->expects($this->at(0))->method('sendMessage')->with('serializedRegister');
        $connMock2->expects($this->at(1))->method('readMessage')->willReturn('serializedRegisterResp');
        $connMock2->expects($this->at(2))->method('sendMessage')->with('serializedTreatment')->will($this->throwException(new ConnectionException("a")));

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->at(0))->method('create')->willReturn($connMock1);
        $connFactoryMock->expects($this->at(1))->method('create')->willReturn($connMock2);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->at(0))->method('serialize')->with(RPC::forRegister('someId', new RegisterFlags(false)))
            ->willReturn('serializedRegister');
        $serializerMock->expects($this->at(1))->method('deserialize')->with('serializedRegisterResp')
            ->willReturn(['s' => 0x01]);
        $serializerMock->expects($this->at(2))->method('serialize')->with(RPC::forTreatment("k", "b", "f", ["a" => 1]))
            ->willReturn('serializedTreatment');
        $serializerMock->expects($this->at(3))->method('serialize')->with(RPC::forRegister('someId', new RegisterFlags(false)))
            ->willReturn('serializedRegister');
        $serializerMock->expects($this->at(4))->method('deserialize')->with('serializedRegisterResp')
            ->willReturn(['s' => 0x01]);
        $serializerMock->expects($this->at(5))->method('serialize')->with(RPC::forTreatment("k", "b", "f", ["a" => 1]))
            ->willReturn('serializedTreatment');

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $v1Manager->getTreatment("k", "b", "f", ["a" => 1]);
    }

}
