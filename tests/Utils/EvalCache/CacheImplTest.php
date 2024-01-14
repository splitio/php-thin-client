<?php

namespace SplitIO\Test\Utils\EvalCache;

use SplitIO\ThinSdk\Utils\EvalCache\CacheImpl;
use SplitIO\ThinSdk\Utils\EvalCache\KeyAttributeCRC32Hasher;
use SplitIO\ThinSdk\Utils\EvalCache\NoEviction;
use PHPUnit\Framework\TestCase;

class CacheImplTest extends TestCase
{
    public function testWithoutConfig()
    {
        $c = new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0));
        $c->set('key', 'f1', null, 'on');
        $this->assertEquals('on', $c->get('key', 'f1', null));
        $this->assertEquals(null, $c->get('key2', 'f1', null));
        $this->assertEquals(null, $c->get('key', 'f1', []));
        $this->assertEquals(null, $c->get('key', 'f1', ['a' => 1]));

        $c->set('key', 'f2', ['a' => 2], 'off');
        $this->assertEquals('off', $c->get('key', 'f2', ['a' => 2]));
        $this->assertEquals(null, $c->get('key', 'f2', ['a' => 3]));
        $this->assertEquals(null, $c->get('key', 'f2', []));
        $this->assertEquals(null, $c->get('key', 'f2', null));

        // only f1 matches for null attributes
        $this->assertEquals(['f1' => 'on', 'f2' => null, 'f3' => null], $c->getMany('key', ['f1', 'f2', 'f3'], null));

        // only f2 matches for ['a' => 1] attributes
        $this->assertEquals(['f1' => null, 'f2' => 'off', 'f3' => null], $c->getMany('key', ['f1', 'f2', 'f3'], ['a' => 2]));

        // nothing matches for [] attributes
        $this->assertEquals(['f1' => null, 'f2' => null, 'f3' => null], $c->getMany('key', ['f1', 'f2', 'f3'], []));

        // *WithConfig methods return null regardless of parameters matching if non-config entries have been stored
        $this->assertEquals(null, $c->getWithConfig('key', 'f1', null));
        $this->assertEquals(
            ['f1' => null, 'f2' => null, 'f3' => null,],
            $c->getManyWithConfig('key', ['f1', 'f2', 'f3'], null)
        );
    }

    public function testWithConfig()
    {
        // setting with config works for both `get`, `getMany`, `getWithConfig`, `getManyWithConfig`
        $c = new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0));
        $c->setWithConfig('key', 'f1', null, 'on', 'some');
        $this->assertEquals('on', $c->get('key', 'f1', null));
        $this->assertEquals(['treatment' => 'on', 'config' => 'some'], $c->getWithConfig('key', 'f1', null));

        $c->setWithConfig('key', 'f2', ['a' => 2], 'off', null);
        $this->assertEquals('off', $c->get('key', 'f2', ['a' => 2]));

        // only f1 matches for null attributes
        $this->assertEquals(
            [
                'f1' => ['treatment' => 'on', 'config' => 'some'],
                'f2' => null,
                'f3' => null,
            ],
            $c->getManyWithConfig('key', ['f1', 'f2', 'f3'], null)
        );

        // only f2 matches for ['a' => 2] attributes
        $this->assertEquals(
            [
                'f1' => null,
                'f2' => ['treatment' => 'off', 'config' => null],
                'f3' => null,
            ],
            $c->getManyWithConfig('key', ['f1', 'f2', 'f3'], ['a' => 2])
        );


        // nothing matches for [] attributes
        $this->assertEquals(['f1' => null, 'f2' => null, 'f3' => null], $c->getManyWithConfig('key', ['f1', 'f2', 'f3'], []));
    }

    public function testByFlagSetsWithoutConfig()
    {
        $c = new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0));
        $c->setFeaturesForFlagSets('key', ['s1', 's2'], null, ['f1' => 'on', 'f2' => 'off'], false);
        $this->assertEquals('on', $c->get('key', 'f1', null));
        $this->assertEquals(null, $c->get('key2', 'f1', null));
        $this->assertEquals(null, $c->get('key', 'f1', ['a' => 1]));
        $this->assertEquals('off', $c->get('key', 'f2', null));

        $results = $c->getByFlagSets(['s1', 's2'], 'key', null, false);
        $this->assertEquals(2, count($results));
        $this->assertEquals('on', $results['f1']);
        $this->assertEquals('off', $results['f2']);

        $results = $c->getByFlagSets(['s1'], 'key', null, false);
        $this->assertEquals(null, $results);
        $results = $c->getByFlagSets(['s2'], 'key', null, false);
        $this->assertEquals(null, $results);
    }

    public function testByFlagSetsWithConfig()
    {
        $c = new CacheImpl(new KeyAttributeCRC32Hasher(), new NoEviction(0));
        $c->setFeaturesForFlagSets('key', ['s1', 's2'], null, ['f1' => ['treatment' => 'on', 'config' => 'some'], 'f2' => ['treatment' => 'off', 'config' => null]], true);
        $this->assertEquals(['treatment' => 'on', 'config' => 'some'], $c->getWithConfig('key', 'f1', null));
        $this->assertEquals(null, $c->getWithConfig('key2', 'f1', null));
        $this->assertEquals(null, $c->getWithConfig('key', 'f1', ['a' => 1]));
        $this->assertEquals(['treatment' => 'off', 'config' => null], $c->getWithConfig('key', 'f2', null));

        $results = $c->getByFlagSets(['s1', 's2'], 'key', null, true);
        $this->assertEquals(2, count($results));
        $this->assertEquals(['treatment' => 'on', 'config' => 'some'], $results['f1']);
        $this->assertEquals(['treatment' => 'off', 'config' => null], $results['f2']);

        $results = $c->getByFlagSets(['s1'], 'key', null, false);
        $this->assertEquals(null, $results);
        $results = $c->getByFlagSets(['s2'], 'key', null, false);
        $this->assertEquals(null, $results);
    }
}
