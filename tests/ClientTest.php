<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinSdk\Client;
use SplitIO\ThinSdk\Utils\ImpressionListener;
use SplitIO\ThinSdk\Models\Impression;
use SplitIO\ThinSdk\Link\Consumer\Manager;
use SplitIO\ThinSdk\Link\Protocol\V1\ImpressionListenerData;

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
        $ilMock->expects($this->at(0))->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456), ['someAttr' => 123]);
        $ilMock->expects($this->at(1))->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457), ['someAttr' => 123]);
        $ilMock->expects($this->at(2))->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458), ['someAttr' => 123]);

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatments('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
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
        $ilMock->expects($this->at(0))->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature1', 'on', 'lab1', 123, 123456), ['someAttr' => 123])
            ->will($this->throwException(new \Exception("qqq")));
        $ilMock->expects($this->at(1))->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature2', 'off', 'lab1', 124, 123457), ['someAttr' => 123])
            ->will($this->throwException(new \Exception("qqq")));
        $ilMock->expects($this->at(2))->method('accept')->with(new Impression('someKey', 'someBuck', 'someFeature3', 'n/a', 'lab1', 125, 123458), ['someAttr' => 123])
            ->will($this->throwException(new \Exception("qqq")));

        $client = new Client($manager, $this->logger, $ilMock);
        $this->assertEquals(
            ['someFeature1' => 'on', 'someFeature2' => 'off', 'someFeature3' => 'n/a'],
            $client->getTreatments('someKey', 'someBuck', ['someFeature1', 'someFeature2', 'someFeature3'], ['someAttr' => 123])
        );
    }

}
