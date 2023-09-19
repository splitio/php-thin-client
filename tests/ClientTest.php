<?php

namespace SplitIO\Test;

use SplitIO\ThinSdk\Client;
use SplitIO\ThinSdk\Utils\ImpressionListener;
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

        $client = new Client($manager, $this->logger, null);
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

        $ilMock = $this->createMock(ImpressionListener::class);
        $ilMock->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                [new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456), ['someAttr' => 123]],
                [new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457), ['someAttr' => 123]],
                [new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458), ['someAttr' => 123]]
            );


        $client = new Client($manager, $this->logger, $ilMock);
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

        $client = new Client($manager, $this->logger, $ilMock);
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
        $ilMock->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                [new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456), ['someAttr' => 123]],
                [new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457), ['someAttr' => 123]],
                [new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458), ['someAttr' => 123]]
            );


        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            [
                'someFeature1' => ['treatment' => 'on', 'config' => null],
                'someFeature2' => ['treatment' => 'off', 'config' => null],
                'someFeature3' => ['treatment' => 'n/a', 'config' => '{"a": 2}']
            ],
            $client->getTreatmentsWithConfig('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
        );
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
        $ilMock
            ->expects($this->exactly(3))
            ->method('accept')
            ->withConsecutive(
                [new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456), ['someAttr' => 123]],
                [new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457), ['someAttr' => 123]],
                [new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458), ['someAttr' => 123]],
            )
            ->will($this->throwException(new \Exception("qqq")));

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
}
