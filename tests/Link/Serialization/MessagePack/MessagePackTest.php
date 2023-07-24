<?php

namespace SplitIO\Test\Link\Serialization\MessagePack;

use SplitIO\ThinSdk\Link\Serialization\MessagePack\MessagePack;
use SplitIO\ThinSdk\Link\Serialization\Serializable;
use SplitIO\ThinSdk\Link\Protocol\Version;
use SplitIO\ThinSdk\Link\Protocol\V1\OpCode;
use SplitIO\ThinSdk\Link\Protocol\V1\RPC;
use SplitIO\ThinSdk\Link\Protocol\V1\RegisterFlags;

use MessagePack\Type\Timestamp;
use PHPUnit\Framework\TestCase;


class MessagePackTest extends TestCase
{


    public function testSerializeRegisterRPC()
    {
        $mp = new MessagePack();
        $serialized = $mp->serialize(RPC::forRegister("some", new RegisterFlags(1 << RegisterFlags::FEEDBACK_IMPRESSIONS)));
        $parsed = $mp->deserialize($serialized);
        $this->assertEquals([
            'v' => Version::V1()->getValue(),
            'o' => OpCode::Register()->getValue(),
            'a' => ['some', 'Splitd_PHP-'. \SplitIO\ThinSdk\Version::CURRENT, 1] 
        ], $parsed);

    }

    public function testSerializeTreatmentRPC()
    {
        $mp = new MessagePack();
        $serialized = $mp->serialize(RPC::forTreatment('some', 'buck', 'feature', ['a' => ['q', 'w', 'e']]));
        $parsed = $mp->deserialize($serialized);
        $this->assertEquals([
            'v' => Version::V1()->getValue(),
            'o' => OpCode::Treatment()->getValue(),
            'a' => ['some', 'buck', 'feature', ['a' => ['q', 'w', 'e']]],
        ], $parsed);
    }

    public function testSerializeTreatmentsRPC()
    {
        $mp = new MessagePack();
        $serialized = $mp->serialize(RPC::forTreatments('some', 'buck', ['f1', 'f2'], ['a' => ['q', 'w', 'e']]));
        $parsed = $mp->deserialize($serialized);
        $this->assertEquals([
            'v' => Version::V1()->getValue(),
            'o' => OpCode::Treatments()->getValue(),
            'a' => ['some', 'buck', ['f1', 'f2'], ['a' => ['q', 'w', 'e']]],
        ], $parsed);
    }

    public function testDatetimeSerialization()
    {
        $mp = new MessagePack();
        $date = new \DateTimeImmutable('2021-05-14');

        $serializableMock = $this->createMock(Serializable::class);
        $serializableMock
            ->expects($this->once())
            ->method('getSerializable')
            ->willReturn(['someData' => $date]);

        $serialized = $mp->serialize($serializableMock);

        $parsed = $mp->deserialize($serialized);
        $this->assertEquals(['someData' => Timestamp::fromDateTime($date)], $parsed);
    }
}
