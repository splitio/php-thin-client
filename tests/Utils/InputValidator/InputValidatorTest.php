<?php

namespace SplitIO\Test\Utils\InputValidator;

use SplitIO\ThinSdk\Utils\InputValidator\InputValidator;
use PHPUnit\Framework\TestCase;

class InputValidatorTest extends TestCase
{
    public function testSanitize()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $inputValidator = new InputValidator($logger);

        $this->assertEquals(null, $inputValidator->sanitize('', 'test'));
        $this->assertEquals('a', $inputValidator->sanitize('    A', 'test'));
        $this->assertEquals('a', $inputValidator->sanitize('A', 'test'));
        $this->assertEquals(null, $inputValidator->sanitize('@FAIL', 'test'));
    }

    public function testSanitizeMany()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $inputValidator = new InputValidator($logger);

        $this->assertEquals(['a', 'test'], $inputValidator->sanitizeMany(['   A  ', '@FAIL', 'TEST'], 'test'));
        $this->assertEquals(null, $inputValidator->sanitizeMany([], 'test'));
        $this->assertEquals(null, $inputValidator->sanitizeMany(['some' => 'some'], 'test'));
    }
}
