<?php

namespace SplitIO\Test\Link\Transfer;
use SplitIO\ThinSdk\Link\Transfer\ConnectionFactory;
use PHPUnit\Framework\TestCase;

class ConnectionFactoryTest extends TestCase
{

    public function testProperTimeouts(): void
    {
	$c = new \ReflectionClass(ConnectionFactory::class);
	$m = $c->getMethod('formatTimeout');
        $m->setAccessible(true);
	$this->assertEquals(['sec' => 1, 'usec' => 0], $m->invoke(null, ['sec' =>1, 'usec' => 0]));
	$this->assertEquals(['sec' => 1, 'usec' => 0], $m->invoke(null, 0));
	$this->assertEquals(['sec' => 1, 'usec' => 0], $m->invoke(null, 1000));
	$this->assertEquals(['sec' => 1, 'usec' => 500000], $m->invoke(null, 1500));
	$this->assertEquals(['sec' => 0, 'usec' => 500000], $m->invoke(null, 500));
	$this->assertEquals(['sec' => 0, 'usec' => 1000], $m->invoke(null, 1));
    }

    public function testInvalidArrayTimeout(): void
    {
    	$c = new \ReflectionClass(ConnectionFactory::class);
	$m = $c->getMethod('formatTimeout');
        $m->setAccessible(true);
        $this->expectExceptionMessage("timeout must either be an int (milliseconds) or an array with keys 'sec' & 'usec'");
        $m->invoke(null, []);
    }

    public function testNonArrayNonIntTimeout(): void
    {
    	$c = new \ReflectionClass(ConnectionFactory::class);
	$m = $c->getMethod('formatTimeout');
        $m->setAccessible(true);
        $this->expectExceptionMessage("timeout must either be an int (milliseconds) or an array with keys 'sec' & 'usec'");
        $m->invoke(null, 98.7);
    }

}
