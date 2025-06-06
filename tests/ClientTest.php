<?php

namespace SplitIO\Test;

use SplitIO\ThinSdk\Client;
use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Utils\Tracing\Tracer;
use SplitIO\ThinSdk\Utils\EvalCache\CacheImpl;
use SplitIO\ThinSdk\Utils\EvalCache\KeyAttributeCRC32Hasher;
use SplitIO\ThinSdk\Utils\EvalCache\NoEviction;
use SplitIO\ThinSdk\Models\Impression;
use SplitIO\ThinSdk\Link\Consumer\Manager;
use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;
use Psr\Log\LoggerInterface;

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    private $logger;

    public function setUp(): void
    {
        $this->logger = $this->createStub(\Psr\Log\LoggerInterface::class);
    }

    public function testGetTreatmentNoImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatment')
            ->with('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
            ->willReturn(['on', null, null]);

        $tracer = $this->createMock(Tracer::class);
        $tracer->expects($this->once())->method('includeArgs')->willReturn(true);
        $tracer->expects($this->once())->method('makeId')->willReturn('some_id');

        $expectations = [
            [
                'id' => 'some_id',
                'method' => Tracer::METHOD_GET_TREATMENT,
                'event' => Tracer::EVENT_START,
                'arguments' => ['someKey', 'someBuck', 'someFeature', ['someAttr' => 123]],
            ],
            [
                'id' => 'some_id',
                'method' => Tracer::METHOD_GET_TREATMENT,
                'event' => Tracer::EVENT_RPC_START,
            ],
            [
                'id' => 'some_id',
                'method' => Tracer::METHOD_GET_TREATMENT,
                'event' => Tracer::EVENT_RPC_END,
            ],
            [
                'id' => 'some_id',
                'method' => Tracer::METHOD_GET_TREATMENT,
                'event' => Tracer::EVENT_END,
            ],
        ];

        $invokedCount = $this->exactly(4);
        $tracer->expects($invokedCount)
            ->method('trace')
            ->willReturnCallback(function ($args) use ($invokedCount, $expectations) {
                match ([$invokedCount->numberOfInvocations() - 1, $args]) {
                    [0, $expectations[0]] => null,
                    [1, $expectations[1]] => null,
                    [2, $expectations[2]] => null,
                    [3, $expectations[3]] => null,
                };
            });

        $client = new Client($manager, $this->logger, null, null, $tracer);
        $this->assertEquals('on', $client->getTreatment('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
    }

    public function testGetTreatmentsNoImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatments')
            ->with('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', null, null],
                'someFeature2' => ['off', null, null],
                'someFeature3' => ['n/a', null, null],
            ]);

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatments('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentWithImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatment')
            ->with('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
            ->willReturn(['on', new ImpressionListenerData('lab1', 123, 123456), null]);

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMock->expects($this->once())->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature', 'on', 'lab1', 123, 123456), ['someAttr' => 123]);

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals('on', $client->getTreatment('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
    }

    public function testGetTreatmentsWithImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatments')
            ->with('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', new ImpressionListenerData('lab1', 123, 123456), null],
                'someFeature2' => ['off', new ImpressionListenerData('lab1', 124, 123457), null],
                'someFeature3' => ['n/a', new ImpressionListenerData('lab1', 125, 123458), null],
            ]);

        $tracer = $this->createMock(Tracer::class);
        $tracer->expects($this->once())->method('includeArgs')->willReturn(true);
        $tracer->expects($this->once())->method('makeId')->willReturn('some_id2');

        $expectations = [
            [
                'id' => 'some_id2',
                'method' => Tracer::METHOD_GET_TREATMENTS,
                'event' => Tracer::EVENT_START,
                'arguments' => ['someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123]],
            ],
            [
                'id' => 'some_id2',
                'method' => Tracer::METHOD_GET_TREATMENTS,
                'event' => Tracer::EVENT_RPC_START,
            ],
            [
                'id' => 'some_id2',
                'method' => Tracer::METHOD_GET_TREATMENTS,
                'event' => Tracer::EVENT_RPC_END,
            ],
            [
                'id' => 'some_id2',
                'method' => Tracer::METHOD_GET_TREATMENTS,
                'event' => Tracer::EVENT_END,
            ]
        ];
        $invokedCount = $this->exactly(4);

        $tracer->expects($invokedCount)
            ->method('trace')
            ->willReturnCallback(function ($args) use ($expectations, $invokedCount) {
                $this->assertEquals($expectations[$invokedCount->numberOfInvocations() - 1], $args);
            });

        $ilExp = [
            [new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456), ['someAttr' => 123]],
            [new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457), ['someAttr' => 123]],
            [new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458), ['someAttr' => 123]]
        ];
        $ilInvokedCount = $this->exactly(3);
        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMock->expects($ilInvokedCount)
            ->method('accept')
            ->willReturnCallback(function ($args) use ($ilExp, $ilInvokedCount) {
                $this->assertEquals($ilExp[$ilInvokedCount->numberOfInvocations() - 1], $args);
            });


        $client = new Client($manager, $this->logger, $ilMock, null, $tracer);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatments('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentWithConfigAndListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentWithConfig')
            ->with('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
            ->willReturn(['on', new ImpressionListenerData('lab1', 123, 123456), '{"a": 1}']);

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMock->expects($this->once())
            ->method('accept')
            ->with(new Impression('someKey', 'someBuck', 'someFeature', 'on', 'lab1', 123, 123456), ['someAttr' => 123]);

        $tracer = $this->createMock(Tracer::class);
        $tracer->expects($this->once())->method('includeArgs')->willReturn(true);
        $tracer->expects($this->once())->method('makeId')->willReturn('some_id3');
        $expectations = [
            [
                'id' => 'some_id3',
                'method' => Tracer::METHOD_GET_TREATMENT_WITH_CONFIG,
                'event' => Tracer::EVENT_START,
                'arguments' => ['someKey', 'someBuck', 'someFeature', ['someAttr' => 123]],
            ],
            [
                'id' => 'some_id3',
                'method' => Tracer::METHOD_GET_TREATMENT_WITH_CONFIG,
                'event' => Tracer::EVENT_RPC_START,
            ],
            [
                'id' => 'some_id3',
                'method' => Tracer::METHOD_GET_TREATMENT_WITH_CONFIG,
                'event' => Tracer::EVENT_RPC_END,
            ],
            [
                'id' => 'some_id3',
                'method' => Tracer::METHOD_GET_TREATMENT_WITH_CONFIG,
                'event' => Tracer::EVENT_END,
            ],
        ];
        $invCount = $this->exactly(4);
        $tracer->expects($invCount)
            ->method('trace')
            ->willReturnCallback(fn($args) => $this->assertEquals($expectations[$invCount->numberOfInvocations() - 1], $args));
        $client = new Client($manager, $this->logger, $ilMock, null, $tracer);
        $this->assertEquals(
            ['treatment' => 'on', 'config' => '{"a": 1}'],
            $client->getTreatmentWithConfig('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsWithConfigAndListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsWithConfig')
            ->with('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', new ImpressionListenerData('lab1', 123, 123456), null],
                'someFeature2' => ['off', new ImpressionListenerData('lab1', 124, 123457), null],
                'someFeature3' => ['n/a', new ImpressionListenerData('lab1', 125, 123458), '{"a": 2}'],
            ]);

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMockInvs = $this->exactly(3);
        $ilExpectations = [
            new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458),
            ['someAttr' => 123],
        ];

        $ilMock->expects($ilMockInvs)
            ->method('accept')
            ->willReturnCallback(fn($args) => $this->assertEquals($ilExpectations[$ilMockInvs->numberOfInvocations() - 1], $args));

        $tracer = $this->createMock(Tracer::class);
        $tracer->expects($this->once())->method('includeArgs')->willReturn(true);
        $tracer->expects($this->once())->method('makeId')->willReturn('some_id4');
        $tracerInvs = $this->exactly(4);
        $tracerExps = [
            [
                'id' => 'some_id4',
                'method' => Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG,
                'event' => Tracer::EVENT_START,
                'arguments' => ['someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123]],
            ],
            [
                'id' => 'some_id4',
                'method' => Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG,
                'event' => Tracer::EVENT_RPC_START,
            ],
            [
                'id' => 'some_id4',
                'method' => Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG,
                'event' => Tracer::EVENT_RPC_END,
            ],
            [
                'id' => 'some_id4',
                'method' => Tracer::METHOD_GET_TREATMENTS_WITH_CONFIG,
                'event' => Tracer::EVENT_END,
            ]
        ];
        $tracer->expects($tracerInvs)
            ->method('trace')
            ->willReturnCallback(fn($args) => $this->assertEquals($tracerExps[$tracerInvs->numberOfInvocations() - 1], $args));

        $client = new Client($manager, $this->logger, $ilMock, null, $tracer);
        $this->assertEquals(
            [
                'someFeature1' => ['treatment' => 'on', 'config' => null],
                'someFeature2' => ['treatment' => 'off', 'config' => null],
                'someFeature3' => ['treatment' => 'n/a', 'config' => '{"a": 2}']
            ],
            $client->getTreatmentsWithConfig('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsByFlagSetNoImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsByFlagSet')
            ->with('someKey', 'someBuck', 'someset', ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', null, null],
                'someFeature2' => ['off', null, null],
                'someFeature3' => ['n/a', null, null],
            ]);

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatmentsByFlagSet('someKey', 'someBuck', 'someSet', ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsByFlagSetWithImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsByFlagSet')
            ->with('someKey', 'someBuck', 'someset', ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', new ImpressionListenerData('lab1', 123, 123456), null],
                'someFeature2' => ['off', new ImpressionListenerData('lab1', 124, 123457), null],
                'someFeature3' => ['n/a', new ImpressionListenerData('lab1', 125, 123458), null],
            ]);

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMockInvs = $this->exactly(3);
        $ilMockExps = [
            new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458),
            ['someAttr' => 123]
        ];

        $ilMock->expects($ilMockInvs)
            ->method('accept')
            ->willReturnCallback(fn($args) => $this->assertEquals($ilMockExps[$ilMockInvs->numberOfInvocations() - 1], $args));

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatmentsByFlagSet('someKey', 'someBuck', 'someSet', ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsWithConfigByFlagSetAndListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsWithConfigByFlagSet')
            ->with('someKey', 'someBuck', 'someset', ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', new ImpressionListenerData('lab1', 123, 123456), null],
                'someFeature2' => ['off', new ImpressionListenerData('lab1', 124, 123457), null],
                'someFeature3' => ['n/a', new ImpressionListenerData('lab1', 125, 123458), '{"a": 2}'],
            ]);


        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMockInvs = $this->exactly(3);
        $ilMockExps = [
            new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458),
            ['someAttr' => 123]
        ];
        $ilMock->expects($ilMockInvs)
            ->method('accept')
            ->willReturnCallback(fn($args) => $this->assertEquals($ilMockExps[$ilMockInvs->numberOfInvocations() - 1], $args));

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            [
                'someFeature1' => ['treatment' => 'on', 'config' => null],
                'someFeature2' => ['treatment' => 'off', 'config' => null],
                'someFeature3' => ['treatment' => 'n/a', 'config' => '{"a": 2}']
            ],
            $client->getTreatmentsWithConfigByFlagSet('someKey', 'someBuck', 'someSet', ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsByFlagSetsNoImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', null, null],
                'someFeature2' => ['off', null, null],
                'someFeature3' => ['n/a', null, null],
            ]);

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatmentsByFlagSets('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsByFlagSetsWithImpListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', new ImpressionListenerData('lab1', 123, 123456), null],
                'someFeature2' => ['off', new ImpressionListenerData('lab1', 124, 123457), null],
                'someFeature3' => ['n/a', new ImpressionListenerData('lab1', 125, 123458), null],
            ]);
        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMockInvs = $this->exactly(3);
        $ilMockExps = [
            new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458),
            ['someAttr' => 123]
        ];
        $ilMock->expects($ilMockInvs)
            ->method('accept')
            ->willReturnCallback(fn($args) => $this->assertEquals($ilMockExps[$ilMockInvs->numberOfInvocations() - 1], $args));

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatmentsByFlagSets('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsWithConfigByFlagSetsAndListener()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsWithConfigByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', new ImpressionListenerData('lab1', 123, 123456), null],
                'someFeature2' => ['off', new ImpressionListenerData('lab1', 124, 123457), null],
                'someFeature3' => ['n/a', new ImpressionListenerData('lab1', 125, 123458), '{"a": 2}'],
            ]);

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMockInvs = $this->exactly(3);
        $ilMockExps = [
            new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458),
            ['someAttr' => 123]
        ];
        $ilMock->expects($ilMockInvs)
            ->method('accept')
            ->willReturnCallback(fn($args) => $this->assertEquals($ilMockExps[$ilMockInvs->numberOfInvocations() - 1], $args));

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            [
                'someFeature1' => ['treatment' => 'on', 'config' => null],
                'someFeature2' => ['treatment' => 'off', 'config' => null],
                'someFeature3' => ['treatment' => 'n/a', 'config' => '{"a": 2}']
            ],
            $client->getTreatmentsWithConfigByFlagSets('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsByFlagSetWithEmptyManagerResult()
    {
        $manager = $this->createMock(Manager::class);
        $manager
            ->expects($this->once())
            ->method('getTreatmentsByFlagSet')
            ->with('someKey', 'someBuck', 'set', ['someAttr' => 123])
            ->willReturn([]);
        $manager
            ->expects($this->once())
            ->method('getTreatmentsWithConfigByFlagSet')
            ->with('someKey', 'someBuck', 'set', ['someAttr' => 123])
            ->willReturn([]);
        $cache = $this->createMock(CacheImpl::class);
        $cache->expects($this->exactly(2))
            ->method('getFeaturesByFlagSets')
            ->with(['set'])
            ->willReturn(null);
        $client = new Client($manager, $this->logger, null, $cache);
        $this->assertEquals([], $client->getTreatmentsByFlagSet('someKey', 'someBuck', 'set', ['someAttr' => 123]));
        $this->assertEquals([], $client->getTreatmentsWithConfigByFlagSet('someKey', 'someBuck', 'set', ['someAttr' => 123]));
    }

    public function testGetTreatmentsByFlagSetsWithEmptyManagerResult()
    {
        $manager = $this->createMock(Manager::class);
        $manager
            ->expects($this->once())
            ->method('getTreatmentsByFlagSets')
            ->with('someKey', 'someBuck', ['set'], ['someAttr' => 123])
            ->willReturn([]);
        $manager
            ->expects($this->once())
            ->method('getTreatmentsWithConfigByFlagSets')
            ->with('someKey', 'someBuck', ['set'], ['someAttr' => 123])
            ->willReturn([]);
        $cache = $this->createMock(CacheImpl::class);
        $cache->expects($this->exactly(2))
            ->method('getFeaturesByFlagSets')
            ->with(['set'])
            ->willReturn(null);
        $client = new Client($manager, $this->logger, null, $cache);
        $this->assertEquals([], $client->getTreatmentsByFlagSets('someKey', 'someBuck', ['set'], ['someAttr' => 123]));
        $this->assertEquals([], $client->getTreatmentsWithConfigByFlagSets('someKey', 'someBuck', ['set'], ['someAttr' => 123]));
    }

    public function testGetTreatmentExceptionReturnsControl()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatment')
            ->with('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
            ->will($this->throwException(new \Exception("abc")));

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals('control', $client->getTreatment('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
    }

    public function testGetTreatmentsExceptionReturnsControl()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatments')
            ->with('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
            ->will($this->throwException(new \Exception("abc")));

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(
            ['someFeature1' => 'control', 'someFeature2' => 'control', 'someFeature3' => 'control'],
            $client->getTreatments('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsByFlagSetExceptionReturnsControl()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsByFlagSet')
            ->with('someKey', 'someBuck', 'someset', ['someAttr' => 123])
            ->will($this->throwException(new \Exception("abc")));

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(
            [],
            $client->getTreatmentsByFlagSet('someKey', 'someBuck', 'someSet', ['someAttr' => 123])
        );
    }

    public function testGetTreatmentsByFlagSetsExceptionReturnsControl()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentsByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->will($this->throwException(new \Exception("abc")));

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(
            [],
            $client->getTreatmentsByFlagSets('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
        );
    }

    public function testGetTreatmentListenerErrorReturnsOk()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatment')
            ->with('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
            ->willReturn(['on', new ImpressionListenerData('lab1', 123, 123456), null]);

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMock->expects($this->once())->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature', 'on', 'lab1', 123, 123456), ['someAttr' => 123])
            ->will($this->throwException(new \Exception("qqq")));

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals('on', $client->getTreatment('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
    }

    public function testGetTreatmentsListenerErrorReturnsOk()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatments')
            ->with('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
            ->willReturn([
                'someFeature1' => ['on', new ImpressionListenerData('lab1', 123, 123456), null],
                'someFeature2' => ['off', new ImpressionListenerData('lab1', 124, 123457), null],
                'someFeature3' => ['n/a', new ImpressionListenerData('lab1', 125, 123458), null],
            ]);

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMockInvs = $this->exactly(3);
        $ilMockExps = [
            new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457),
            ['someAttr' => 123],
            new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458),
            ['someAttr' => 123]
        ];
        $ilMock->expects($ilMockInvs)
            ->method('accept')
            ->willReturnCallback(fn($args) => $this->assertEquals($ilMockExps[$ilMockInvs->numberOfInvocations() - 1], $args));

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatments('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
        );
    }

    public function testTrack()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('track')
            ->with('someKey', 'someTrafficType', 'someEventType', 1.25, ['someProp' => 123])
            ->willReturn(true);

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(true, $client->track('someKey', 'someTrafficType', 'someEventType', 1.25, ['someProp' => 123]));
    }

    public function testTrackInvalidProperties()
    {
        $manager = $this->createMock(Manager::class);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')
            ->with("error validating event properties: The maximum size allowed for the properties is 32768 bytes. Current one is 32813 bytes. Event not queued");

        $largePropertySet = [];
        for ($i = 0; $i < 50000; $i++) {
            $largePropertySet["xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" . $i] = $i;
        }

        $client = new Client($manager, $logger, null);
        $this->assertEquals(false, $client->track('someKey', 'someTrafficType', 'someEventType', 1.25, $largePropertySet));
    }

    public function testTrackManagerException()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('track')
            ->with('someKey', 'someTrafficType', 'someEventType', 1.25, ['a' => 1])
            ->will($this->throwException(new \Exception("abc")));

        $client = new Client($manager, $this->logger, null);
        $this->assertEquals(false, $client->track('someKey', 'someTrafficType', 'someEventType', 1.25, ['a' => 1]));
    }

    public function testGetTreatmentCacheEnabled()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatment')
            ->with('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
            ->willReturn(['on', null, null]);

        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));

        // 2 calls, expecting only one in manager
        $this->assertEquals('on', $client->getTreatment('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
        $this->assertEquals('on', $client->getTreatment('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
    }

    public function testGetTreatmentWithConfigCacheEnabled()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())->method('getTreatmentWithConfig')
            ->with('someKey', 'someBuck', 'someFeature', ['someAttr' => 123])
            ->willReturn(['on', null, 'some']);

        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));

        // 2 calls to getTreatmentWithConfig, 1 to getTreatment with same input => only one call to link manager
        $this->assertEquals(['treatment' => 'on', 'config' => 'some'], $client->getTreatmentWithConfig('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
        $this->assertEquals(['treatment' => 'on', 'config' => 'some'], $client->getTreatmentWithConfig('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
        $this->assertEquals('on', $client->getTreatment('someKey', 'someBuck', 'someFeature', ['someAttr' => 123]));
    }

    public function testGetTreatmentsCacheEnabled()
    {
        $manager = $this->createMock(Manager::class);
        $mInvCount = $this->exactly(2);
        $mExps = [
            [
                'args' => ['someKey', 'someBuck', ['f1', 'f2'], ['someAttr' => 123]],
                'ret' => ['f1' => ['on', null, 'some'], 'f2' => ['off', null, null]],
            ],
            [
                'args' => ['someKey', 'someBuck', ['f3'], ['someAttr' => 123]],
                'ret' => ['f3' => ['na', null, 'another']],
            ],
        ];

        $manager->expects($mInvCount)
            ->method('getTreatments')
            ->willReturnCallback(function (...$args) use ($mExps, $mInvCount) {
                $params = $mExps[$mInvCount->numberOfInvocations() - 1];
                $this->assertEquals($params['args'], $args);
                return $params['ret'];
            });

        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatments('someKey', 'someBuck', ['f1', 'f2'], ['someAttr' => 123]));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatments('someKey', 'someBuck', ['f1', 'f2'], ['someAttr' => 123]));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off', 'f3' => 'na'], $client->getTreatments('someKey', 'someBuck', ['f1', 'f2', 'f3'], ['someAttr' => 123]));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off', 'f3' => 'na'], $client->getTreatments('someKey', 'someBuck', ['f1', 'f2', 'f3'], ['someAttr' => 123]));
    }

    public function testGetTreatmentsWithConfigCacheEnabled()
    {
        $manager = $this->createMock(Manager::class);
        $mInvCount = $this->exactly(2);
        $mExps = [
            [
                'args' => ['someKey', 'someBuck', ['f1', 'f2'], ['someAttr' => 123]],
                'ret' => ['f1' => ['on', null, 'some'], 'f2' => ['off', null, null]],
            ],
            [
                'args' => ['someKey', 'someBuck', ['f3'], ['someAttr' => 123]],
                'ret' => ['f3' => ['na', null, 'another']],
            ],
        ];
        $manager->expects($mInvCount)
            ->method('getTreatmentsWithConfig')
            ->willReturnCallback(function (...$args) use ($mExps, $mInvCount) {
                $params = $mExps[$mInvCount->numberOfInvocations() - 1];
                $this->assertEquals($params['args'], $args);
                return $params['ret'];
            });

        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));
        $this->assertEquals(
            [
                'f1' => ['treatment' => 'on', 'config' => 'some'],
                'f2' => ['treatment' => 'off', 'config' => null],
            ],
            $client->getTreatmentsWithConfig('someKey', 'someBuck', ['f1', 'f2'], ['someAttr' => 123])
        );
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatments('someKey', 'someBuck', ['f1', 'f2'], ['someAttr' => 123]));
        $this->assertEquals(
            [
                'f1' => ['treatment' => 'on', 'config' => 'some'],
                'f2' => ['treatment' => 'off', 'config' => null],
                'f3' => ['treatment' => 'na', 'config' => 'another'],
            ],
            $client->getTreatmentsWithConfig('someKey', 'someBuck', ['f1', 'f2', 'f3'], ['someAttr' => 123])
        );
        $this->assertEquals(['f1' => 'on', 'f2' => 'off', 'f3' => 'na'], $client->getTreatments('someKey', 'someBuck', ['f1', 'f2', 'f3'], ['someAttr' => 123]));
    }

    public function testCacheForGetTreatmentsByFlagSet()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsByFlagSet')
            ->with('someKey', 'someBuck', 'set_1', ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, null], 'f2' => ['off', null, null]]);

        $cache = $this->createMock(CacheImpl::class);
        $cache->expects($this->exactly(2))
            ->method('getFeaturesByFlagSets')
            ->with(['set_1'])
            ->willReturnOnConsecutiveCalls(null, ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('getMany')
            ->with('someKey', ['f1', 'f2'], ['someAttr' => 123])
            ->willReturn(['f1' => 'on', 'f2' => 'off']);
        $cache->expects($this->once())
            ->method('setFeaturesForFlagSets')
            ->with(['set_1'], ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('setMany')
            ->with('someKey', ['someAttr' => 123], ['f1' => 'on', 'f2' => 'off']);

        $client = new Client($manager, $this->logger, null, $cache);
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSet('someKey', 'someBuck', ' set_1  ', ['someAttr' => 123]));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSet('someKey', 'someBuck', 'SET_1', ['someAttr' => 123]));

        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsByFlagSet')
            ->with('someKey', 'someBuck', 'set_1', ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, null], 'f2' => ['off', null, null]]);
        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSet('someKey', 'someBuck', ' set_1  ', ['someAttr' => 123]));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSet('someKey', 'someBuck', 'SET_1', ['someAttr' => 123]));
    }

    public function testCacheForGetTreatmentsWithConfigByFlagSet()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsWithConfigByFlagSet')
            ->with('someKey', 'someBuck', 'set_1', ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, 'some'], 'f2' => ['off', null, null]]);

        $cache = $this->createMock(CacheImpl::class);
        $cache->expects($this->exactly(2))
            ->method('getFeaturesByFlagSets')
            ->with(['set_1'])
            ->willReturnOnConsecutiveCalls(null, ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('setFeaturesForFlagSets')
            ->with(['set_1'], ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('getManyWithConfig')
            ->with('someKey', ['f1', 'f2'], ['someAttr' => 123])
            ->willReturn(['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]]);
        $cache->expects($this->once())
            ->method('setManyWithConfig')
            ->with(
                'someKey',
                ['someAttr' => 123],
                [
                    'f1' => ['treatment' => 'on', 'config' => 'some'],
                    'f2' => ['treatment' => 'off', 'config' => null]
                ]
            );

        $client = new Client($manager, $this->logger, null, $cache);
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSet('someKey', 'someBuck', ' set_1  ', ['someAttr' => 123])
        );
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSet('someKey', 'someBuck', ' set_1  ', ['someAttr' => 123])
        );

        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsWithConfigByFlagSet')
            ->with('someKey', 'someBuck', 'set_1', ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, 'some'], 'f2' => ['off', null, null]]);
        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSet('someKey', 'someBuck', ' set_1  ', ['someAttr' => 123])
        );
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSet('someKey', 'someBuck', ' set_1  ', ['someAttr' => 123])
        );
    }

    public function testCacheForGetTreatmentsByFlagSets()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, null], 'f2' => ['off', null, null]]);

        $cache = $this->createMock(CacheImpl::class);
        $cache->expects($this->exactly(2))
            ->method('getFeaturesByFlagSets')
            ->with(['set_1', 'set_2'])
            ->willReturnOnConsecutiveCalls(null, ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('getMany')
            ->with('someKey', ['f1', 'f2'], ['someAttr' => 123])
            ->willReturn(['f1' => 'on', 'f2' => 'off']);
        $cache->expects($this->once())
            ->method('setFeaturesForFlagSets')
            ->with(['set_1', 'set_2'], ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('setMany')
            ->with('someKey', ['someAttr' => 123], ['f1' => 'on', 'f2' => 'off']);

        $client = new Client($manager, $this->logger, null, $cache);
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSets('someKey', 'someBuck', [' set_1   ', 'set_2', '@FAIL'], ['someAttr' => 123]));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSets('someKey', 'someBuck', ['SET_1', 'set_2', '   '], ['someAttr' => 123]));

        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, null], 'f2' => ['off', null, null]]);
        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSets('someKey', 'someBuck', [' set_1   ', 'set_2', '@FAIL'], ['someAttr' => 123]));
        $this->assertEquals(['f1' => 'on', 'f2' => 'off'], $client->getTreatmentsByFlagSets('someKey', 'someBuck', ['SET_1', 'set_2', '   '], ['someAttr' => 123]));
    }

    public function testCacheForGetTreatmentsWithConfigByFlagSets()
    {
        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsWithConfigByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, 'some'], 'f2' => ['off', null, null]]);

        $cache = $this->createMock(CacheImpl::class);
        $cache->expects($this->exactly(2))
            ->method('getFeaturesByFlagSets')
            ->with(['set_1', 'set_2'])
            ->willReturnOnConsecutiveCalls(null, ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('setFeaturesForFlagSets')
            ->with(['set_1', 'set_2'], ['f1', 'f2']);
        $cache->expects($this->once())
            ->method('getManyWithConfig')
            ->with('someKey', ['f1', 'f2'], ['someAttr' => 123])
            ->willReturn(['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]]);
        $cache->expects($this->once())
            ->method('setManyWithConfig')
            ->with(
                'someKey',
                ['someAttr' => 123],
                [
                    'f1' => ['treatment' => 'on', 'config' => 'some'],
                    'f2' => ['treatment' => 'off', 'config' => null]
                ]
            );

        $client = new Client($manager, $this->logger, null, $cache);
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSets('someKey', 'someBuck', [' set_1   ', 'set_2', '@FAIL'], ['someAttr' => 123])
        );
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSets('someKey', 'someBuck', [' set_1   ', 'set_2', '    '], ['someAttr' => 123])
        );

        $manager = $this->createMock(Manager::class);
        $manager->expects($this->once())
            ->method('getTreatmentsWithConfigByFlagSets')
            ->with('someKey', 'someBuck', ['set_1', 'set_2'], ['someAttr' => 123])
            ->willReturn(['f1' => ['on', null, 'some'], 'f2' => ['off', null, null]]);
        $client = new Client($manager, $this->logger, null, new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0)));
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSets('someKey', 'someBuck', [' set_1   ', 'set_2', '@FAIL'], ['someAttr' => 123])
        );
        $this->assertEquals(
            ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]],
            $client->getTreatmentsWithConfigByFlagSets('someKey', 'someBuck', [' set_1   ', 'set_2', '    '], ['someAttr' => 123])
        );
    }

    public function testInputValidatorForFlagSets()
    {
        $manager = $this->createMock(Manager::class);
        $client = new Client($manager, $this->logger, null, null);
        $this->assertEquals([], $client->getTreatmentsByFlagSet('someKey', 'someBuck', '@FAIL', ['someAttr' => 123]));
        $this->assertEquals([], $client->getTreatmentsWithConfigByFlagSet('someKey', 'someBuck', '@FAIL', ['someAttr' => 123]));
        $this->assertEquals([], $client->getTreatmentsByFlagSets('someKey', 'someBuck', ['@FAIL', '    '], ['someAttr' => 123]));
        $this->assertEquals([], $client->getTreatmentsWithConfigByFlagSets('someKey', 'someBuck', ['@FAIL', '    '], ['someAttr' => 123]));
    }
}
