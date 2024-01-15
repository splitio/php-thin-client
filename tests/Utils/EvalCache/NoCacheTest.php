<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Utils\EvalCache\NoCache;
use PHPUnit\Framework\TestCase;

class NoCacheTest extends TestCase
{
    public function testNoCache()
    {
        $c = new NoCache();
        $c->set('key', 'f1', null, 'on');
        $c->set('key', 'f2', null, 'off');
        $this->assertEquals(null, $c->get('key', 'f1', null));
        $this->assertEquals(null, $c->get('key', 'f2', ['a' => 1]));
        $this->assertEquals(['f1' => null, 'f2' => null], $c->getMany('key', ['f1', 'f2'], null));
        $c->setMany('key', null, ['f1' => 'on', 'f2' => 'off']);
        $this->assertEquals(null, $c->get('key', 'f1', null));
        $this->assertEquals(null, $c->get('key', 'f2', ['a' => 1]));
        $this->assertEquals(['f1' => null, 'f2' => null], $c->getMany('key', ['f1', 'f2'], null));

        $c->setWithConfig('key', 'f1', null, 'on', 'some');
        $c->setWithConfig('key', 'f2', null, 'off', 'some');
        $this->assertEquals(null, $c->get('key', 'f1', null));
        $this->assertEquals(null, $c->get('key', 'f2', ['a' => 1]));
        $this->assertEquals(['f1' => null, 'f2' => null], $c->getMany('key', ['f1', 'f2'], null));
        $this->assertEquals(null, $c->getWithConfig('key', 'f1', null));
        $this->assertEquals(null, $c->getWithConfig('key', 'f2', null));
        $this->assertEquals(['f1' => null, 'f2' => null], $c->getManyWithConfig('key', ['f1', 'f2'], null));

        $c->setManyWithConfig('key', null, [
            'f1' => ['treatment' => 'on', 'config' => 'some'],
            'f2' => ['treatment' => 'off', 'config' => null],
        ]);
        $this->assertEquals(null, $c->get('key', 'f1', null));
        $this->assertEquals(null, $c->get('key', 'f2', ['a' => 1]));
        $this->assertEquals(['f1' => null, 'f2' => null], $c->getMany('key', ['f1', 'f2'], null));
        $this->assertEquals(null, $c->getWithConfig('key', 'f1', null));
        $this->assertEquals(null, $c->getWithConfig('key', 'f2', null));
        $this->assertEquals(['f1' => null, 'f2' => null], $c->getManyWithConfig('key', ['f1', 'f2'], null));

        $c->setFeaturesForFlagSets(['s1', 's2'], ['f1', 'f2']);
        $this->assertEquals(null, $c->getFeaturesByFlagSets(['s1', 's2']));
        $this->assertEquals(null, $c->getFeaturesByFlagSets(['s2', 's1']));
    }
}
