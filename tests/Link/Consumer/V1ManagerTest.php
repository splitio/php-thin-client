<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Config\Utils;
use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Link\Protocol\V1\RPC;
use SplitIO\ThinSdk\Link\Protocol\V1\RegisterFlags;
use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;
use SplitIO\ThinSdk\Link\Consumer\V1Manager;
use SplitIO\ThinSdk\Link\Transfer\ConnectionFactory;
use SplitIO\ThinSdk\Link\Transfer\RawConnection;
use SplitIO\ThinSdk\Link\Transfer\ConnectionException;
use SplitIO\ThinSdk\Link\Serialization\SerializerFactory;
use SplitIO\ThinSdk\Link\Serialization\Serializer;
use \SplitIO\ThinSdk\SplitView;

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
        $connMock->expects($this->exactly(9))
            ->method('sendMessage')
            ->withConsecutive(
                ['serializedRegister'],
                ['serializedTreatment'],
                ['serializedTreatments'],
                ['serializedTreatmentWithConfig'],
                ['serializedTreatmentsWithConfig'],
                ['serializedTreatmentsByFlagSet'],
                ['serializedTreatmentsWithConfigByFlagSet'],
                ['serializedTreatmentsByFlagSets'],
                ['serializedTreatmentsWithConfigByFlagSets']
            );
        $connMock->expects($this->exactly(9))
            ->method('readMessage')
            ->willReturnOnConsecutiveCalls(
                'serializedRegisterResp',
                'serializedTreatmentResp',
                'serializedTreatmentsResp',
                'serilaizedTreatmentWithConfigResp',
                'serializedTreatmentsWithConfigResp',
                'serializedTreatmentsByFlagSetResp',
                'serializedTreatmentsWithConfigByFlagSetResp',
                'serializedTreatmentsByFlagSetsResp',
                'serializedTreatmentsWithConfigByFlagSetsResp',
            );

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(9))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forTreatment("k", "b", "f", ["a" => 1])],
                [RPC::forTreatments("k", "b", ["f1", "f2", "f3"], ["a" => 1])],
                [RPC::forTreatmentWithConfig("k", "b", "f", ["a" => 1])],
                [RPC::forTreatmentsWithConfig("k", "b", ["f1", "f2", "f3"], ["a" => 1])],
                [RPC::forTreatmentsByFlagSet("k", "b", "s", ["a" => 1])],
                [RPC::forTreatmentsWithConfigByFlagSet("k", "b", "s", ["a" => 1])],
                [RPC::forTreatmentsByFlagSets("k", "b", ["s1", "s2"], ["a" => 1])],
                [RPC::forTreatmentsWithConfigByFlagSets("k", "b", ["s1", "s2"], ["a" => 1])],
            )
            ->willReturnOnConsecutiveCalls(
                'serializedRegister',
                'serializedTreatment',
                'serializedTreatments',
                'serializedTreatmentWithConfig',
                'serializedTreatmentsWithConfig',
                'serializedTreatmentsByFlagSet',
                'serializedTreatmentsWithConfigByFlagSet',
                'serializedTreatmentsByFlagSets',
                'serializedTreatmentsWithConfigByFlagSets'
            );

        $serializerMock->expects($this->exactly(9))
            ->method('deserialize')
            ->withConsecutive(
                ['serializedRegisterResp'],
                ['serializedTreatmentResp'],
                ['serializedTreatmentsResp'],
                ['serilaizedTreatmentWithConfigResp'],
                ['serializedTreatmentsWithConfigResp'],
                ['serializedTreatmentsByFlagSetResp'],
                ['serializedTreatmentsWithConfigByFlagSetResp'],
                ['serializedTreatmentsByFlagSetsResp'],
                ['serializedTreatmentsWithConfigByFlagSetsResp']
            )
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01, 'p' => ['t' => 'on']],
                ['s' => 0x01, 'p' => ['r' => [['t' => 'on'], ['t' => 'on'], ['t' => 'off']]]],
                ['s' => 0x01, 'p' => ['t' => 'on', 'c' => '{"a": 1}']],
                ['s' => 0x01, 'p' => ['r' => [['t' => 'on'], ['t' => 'on'], ['t' => 'off', 'c' => '{"a": 2}']]]],
                ['s' => 0x01, 'p' => ['r' => ['f1' => ['t' => 'on'], 'f2' => ['t' => 'on'], 'f3' => ['t' => 'off', 'c' => '{"a": 2}']]]],
                ['s' => 0x01, 'p' => ['r' => ['f1' => ['t' => 'on'], 'f2' => ['t' => 'on'], 'f3' => ['t' => 'off', 'c' => '{"a": 2}']]]],
                ['s' => 0x01, 'p' => ['r' => ['f1' => ['t' => 'on'], 'f2' => ['t' => 'on'], 'f3' => ['t' => 'off', 'c' => '{"a": 2}']]]],
                ['s' => 0x01, 'p' => ['r' => ['f1' => ['t' => 'on'], 'f2' => ['t' => 'on'], 'f3' => ['t' => 'off', 'c' => '{"a": 2}']]]]
            );

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(['on', null], $v1Manager->getTreatment("k", "b", "f", ["a" => 1]));
        $this->assertEquals(
            ['f1' => ['on', null], 'f2' => ['on', null], 'f3' => ['off', null]],
            $v1Manager->getTreatments('k', 'b', ['f1', 'f2', 'f3'], ['a' => 1])
        );
        $this->assertEquals(['on', null, '{"a": 1}'], $v1Manager->getTreatmentWithConfig("k", "b", "f", ["a" => 1]));
        $this->assertEquals(
            ['f1' => ['on', null, null], 'f2' => ['on', null, null], 'f3' => ['off', null, '{"a": 2}']],
            $v1Manager->getTreatmentsWithConfig('k', 'b', ['f1', 'f2', 'f3'], ['a' => 1])
        );
        $this->assertEquals(
            ['f1' => ['on', null], 'f2' => ['on', null], 'f3' => ['off', null]],
            $v1Manager->getTreatmentsByFlagSet('k', 'b', "s", ['a' => 1])
        );
        $this->assertEquals(
            ['f1' => ['on', null, null], 'f2' => ['on', null, null], 'f3' => ['off', null, '{"a": 2}']],
            $v1Manager->getTreatmentsWithConfigByFlagSet('k', 'b', "s", ['a' => 1])
        );
        $this->assertEquals(
            ['f1' => ['on', null], 'f2' => ['on', null], 'f3' => ['off', null]],
            $v1Manager->getTreatmentsByFlagSets('k', 'b', ["s1", "s2"], ['a' => 1])
        );
        $this->assertEquals(
            ['f1' => ['on', null, null], 'f2' => ['on', null, null], 'f3' => ['off', null, '{"a": 2}']],
            $v1Manager->getTreatmentsWithConfigByFlagSets('k', 'b', ["s1", "s2"], ['a' => 1])
        );
    }

    public function testHappyExchangeWithImpListener(): void
    {
        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->exactly(3))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedTreatment'], ['serializedTreatments']);
        $connMock->expects($this->exactly(3))
            ->method('readMessage')
            ->willReturnOnConsecutiveCalls('serializedRegisterResp', 'serializedTreatmentResp', 'serializedTreatmentsResp');
        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(3))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(true))],
                [RPC::forTreatment("k", "b", "f", ["a" => 1])],
                [RPC::forTreatments("k", "b", ["f1", "f2", "f3"], ["a" => 1])],
            )
            ->willReturnOnConsecutiveCalls('serializedRegister', 'serializedTreatment', 'serializedTreatments');
        $serializerMock->expects($this->exactly(3))
            ->method('deserialize')
            ->withConsecutive(['serializedRegisterResp'], ['serializedTreatmentResp'], ['serializedTreatmentsResp'])
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01, 'p' => ['t' => 'on', 'l' => ['l' => 'lab1', 'c' => 123, 'm' => 1234]]],
                ['s' => 0x01, 'p' => ['r' => [
                    ['t' => 'on', 'l' => ['l' => 'lab1', 'c' => 123, 'm' => 1234]],
                    ['t' => 'on', 'l' => ['l' => 'lab2', 'c' => 124, 'm' => 1235]],
                    ['t' => 'off', 'l' => ['l' => 'lab3', 'c' => 125, 'm' => 1236]],
                ]]]
            );
        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $ilMock = $this->createMock(ImpressionListener::class);
        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::fromArray(['impressionListener' => $ilMock]), $this->logger);
        $this->assertEquals(
            ['on', new ImpressionListenerData('lab1', 123, 1234)],
            $v1Manager->getTreatment("k", "b", "f", ["a" => 1])
        );
        $this->assertEquals(
            [
                'f1' => ['on', new ImpressionListenerData('lab1', 123, 1234)],
                'f2' => ['on', new ImpressionListenerData('lab2', 124, 1235)],
                'f3' => ['off', new ImpressionListenerData('lab3', 125, 1236)],
            ],
            $v1Manager->getTreatments('k', 'b', ['f1', 'f2', 'f3'], ['a' => 1])
        );
    }

    public function testRegisterFailCrashes(): void
    {
        $this->expectException(ConnectionException::class);

        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->once())
            ->method('sendMessage')
            ->with('serializedRegister')
            ->will($this->throwException(new ConnectionException("some")));

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->once())
            ->method('serialize')
            ->with(RPC::forRegister('someId', new RegisterFlags(false)))
            ->willReturn('serializedRegister');

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $v1Manager->getTreatment("k", "b", "f", ["a" => 1]);
    }

    public function testPostRegisterRPCsAreRetried(): void
    {
        $connMock1 = $this->createMock(RawConnection::class);
        $connMock1->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedTreatment'])
            ->will($this->onConsecutiveCalls(
                'serializedRegisterResp',
                $this->throwException(new ConnectionException("a"))
            ));
        $connMock1->expects($this->once())->method('readMessage')->willReturn('serializedRegisterResp');

        $connMock2 = $this->createMock(RawConnection::class);
        $connMock2->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedTreatment']);
        $connMock2->expects($this->exactly(2))
            ->method('readMessage')
            ->willReturnOnConsecutiveCalls('serializedRegisterResp', 'serializedTreatmentResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->exactly(2))
            ->method('create')->willReturnOnConsecutiveCalls($connMock1, $connMock2);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(4))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forTreatment("k", "b", "f", ["a" => 1])],
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forTreatment("k", "b", "f", ["a" => 1])],
            )
            ->willReturnOnConsecutiveCalls('serializedRegister', 'serializedTreatment', 'serializedRegister', 'serializedTreatment');

        $serializerMock->expects($this->exactly(3))
            ->method('deserialize')
            ->withConsecutive(['serializedRegisterResp'], ['serializedRegisterResp'], ['serializedTreatmentResp'])
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01],
                ['s' => 0x01, 'p' => ['t' => 'on']],
            );

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(['on', null], $v1Manager->getTreatment("k", "b", "f", ["a" => 1]));
    }

    public function test2FailuresCrash(): void
    {
        $this->expectException(ConnectionException::class);

        $connMock1 = $this->createMock(RawConnection::class);
        $connMock1->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedTreatment'])
            ->will($this->onConsecutiveCalls(
                'serializedRegisterResp',
                $this->throwException(new ConnectionException("a"))
            ));
        $connMock1->expects($this->once())->method('readMessage')->willReturn('serializedRegisterResp');

        $connMock2 = $this->createMock(RawConnection::class);
        $connMock2->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedTreatment'])
            ->will($this->onConsecutiveCalls(
                'serializedRegisterResp',
                $this->throwException(new ConnectionException("a"))
            ));
        $connMock2->expects($this->once())->method('readMessage')->willReturn('serializedRegisterResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls($connMock1, $connMock2);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(4))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forTreatment("k", "b", "f", ["a" => 1])],
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forTreatment("k", "b", "f", ["a" => 1])],
            )
            ->willReturnOnConsecutiveCalls('serializedRegister', 'serializedTreatment', 'serializedRegister', 'serializedTreatment');

        $serializerMock->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(['serializedRegisterResp'], ['serializedRegisterResp'])
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01],
            );

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $v1Manager->getTreatment("k", "b", "f", ["a" => 1]);
    }

    public function testTrack(): void
    {
        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedTrack']);
        $connMock->expects($this->exactly(2))
            ->method('readMessage')
            ->willReturnOnConsecutiveCalls('serializedRegisterResp', 'serializedTrackResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(2))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forTrack("k", "tt", "et", 1.25, ["a" => 1])],
            )
            ->willReturnOnConsecutiveCalls('serializedRegister', 'serializedTrack');

        $serializerMock->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(['serializedRegisterResp'], ['serializedTrackResp'])
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01, 'p' => ['s' => true]],
            );

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(true, $v1Manager->track('k', 'tt', 'et', 1.25, ['a' => 1]));
    }

    public function testSplitNames(): void
    {
        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedSplitNames']);
        $connMock->expects($this->exactly(2))
            ->method('readMessage')
            ->willReturnOnConsecutiveCalls('serializedRegisterResp', 'serializedSplitNamesResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(2))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forSplitNames()],
            )
            ->willReturnOnConsecutiveCalls('serializedRegister', 'serializedSplitNames');

        $serializerMock->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(['serializedRegisterResp'], ['serializedSplitNamesResp'])
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01, 'p' => ['n' => ['s1', 's2']]],
            );

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(['s1', 's2'], $v1Manager->splitNames());
    }

    public function testSplit(): void
    {
        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedSplit']);
        $connMock->expects($this->exactly(2))
            ->method('readMessage')
            ->willReturnOnConsecutiveCalls('serializedRegisterResp', 'serializedSplitResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(2))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forSplit('someName')],
            )
            ->willReturnOnConsecutiveCalls('serializedRegister', 'serializedSplit');

        $serializerMock->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(['serializedRegisterResp'], ['serializedSplitResp'])
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01, 'p' => [
                    'n' => 'someName',
                    't' => 'someTrafficType',
                    'k' => true,
                    's' => ['on', 'off'],
                    'c' => 123,
                    'f' => ['on' => 'some'],
                    'd' => 'on',
                    'e' => ['s1', 's2']
                ]],
            );

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(
            new SplitView('someName', 'someTrafficType', true, ['on', 'off'], 123, 'on', ['s1', 's2'], ['on' => 'some']),
            $v1Manager->split('someName')
        );
    }
    public function testSplits(): void
    {
        $connMock = $this->createMock(RawConnection::class);
        $connMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(['serializedRegister'], ['serializedSplits']);
        $connMock->expects($this->exactly(2))
            ->method('readMessage')
            ->willReturnOnConsecutiveCalls('serializedRegisterResp', 'serializedSplitsResp');

        $connFactoryMock = $this->createMock(ConnectionFactory::class);
        $connFactoryMock->expects($this->once())->method('create')->willReturn($connMock);

        $serializerMock = $this->createMock(Serializer::class);
        $serializerMock->expects($this->exactly(2))
            ->method('serialize')
            ->withConsecutive(
                [RPC::forRegister('someId', new RegisterFlags(false))],
                [RPC::forSplits()],
            )
            ->willReturnOnConsecutiveCalls('serializedRegister', 'serializedSplits');

        $serializerMock->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(['serializedRegisterResp'], ['serializedSplitsResp'])
            ->willReturnOnConsecutiveCalls(
                ['s' => 0x01],
                ['s' => 0x01, 'p' => ['s' => [
                    [
                        'n' => 'someName',
                        't' => 'someTrafficType',
                        'k' => true,
                        's' => ['on', 'off'],
                        'c' => 123,
                        'f' => ['on' => 'some'],
                        'd' => 'on',
                        'e' => ['s1', 's2'],
                    ],
                    [
                        'n' => 'someName2',
                        't' => 'someTrafficType',
                        'k' => false,
                        's' => ['on', 'off'],
                        'c' => 124,
                        'f' => null,
                        'd' => 'off',
                        'e' => null,
                    ],
                ]]],
            );

        $serializerFactoryMock = $this->createMock(SerializerFactory::class);
        $serializerFactoryMock->expects($this->once())->method('create')->willReturn($serializerMock);

        $v1Manager = new V1Manager($connFactoryMock, $serializerFactoryMock, Utils::default(), $this->logger);
        $this->assertEquals(
            [
                new SplitView('someName', 'someTrafficType', true, ['on', 'off'], 123, 'on', ['s1', 's2'], ['on' => 'some']),
                new SplitView('someName2', 'someTrafficType', false, ['on', 'off'], 124, 'off', [], null),
            ],
            $v1Manager->splits()
        );
    }
}
