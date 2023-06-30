<?php

namespace SplitIO\Test\Link\Consumer;

use SplitIO\ThinClient\Foundation\Lang\Enforce;

use PHPUnit\Framework\TestCase;

class EnforceTest extends TestCase
{
    public function testIsStringOk()
    {
        $this->assertEquals("some", Enforce::isString("some"));
        $this->assertEquals("", Enforce::isString(""));
    }

    public function testIsStringNullThrows()
    {
        $this->expectException(\Exception::class);
        Enforce::isString(null);
    }

    public function testIsStringNotStringThrows()
    {
        $this->expectException(\Exception::class);
        Enforce::isString(42);
    }

    public function testIsArrayOk()
    {
        $this->assertEquals([1, 2, 3], Enforce::isArray([1, 2, 3]));
        $this->assertEquals(['asd', 'qwe', 'zxc'], Enforce::isArray(['asd', 'qwe', 'zxc']));
        $this->assertEquals(['a' => 1, 'b' => 2], Enforce::isArray(['a' => 1, 'b' => 2]));
        $this->assertEquals([], Enforce::isArray([]));
    }

    public function testIsArrayNullThrows()
    {
        $this->expectException(\Exception::class);
        Enforce::isArray(null);
    }

    public function testIsArrayNotArrayThrows()
    {
        $this->expectException(\Exception::class);
        Enforce::isArray(new \stdClass());
    }

    public function testIsIntOk()
    {
        $this->assertEquals(42, Enforce::isInt(42));
        $this->assertEquals(0, Enforce::isInt(0));
        $this->assertEquals(-1, Enforce::isInt(-1));
    }

    public function testIsIntNullThrows()
    {
        $this->expectException(\Exception::class);
        Enforce::isInt(null);
    }

    public function testIsIntNotInt()
    {
        $this->expectException(\Exception::class);
        Enforce::isArray('42');
    }

}
