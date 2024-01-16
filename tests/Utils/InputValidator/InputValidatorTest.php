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

        $logger
            ->expects($this->exactly(6))
            ->method('warning')
            ->withConsecutive(
                ['test: you passed "", Flag Set must adhere to the regular expressions ' .
                    '{/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                    'have a max length of 50 characters. "" was discarded.'],
                ['test: Flag Set name "    A" has extra whitespace, trimming.'],
                ['test: Flag Set name "    A" should be all lowercase - converting string to lowercase.'],
                ['test: Flag Set name "A" should be all lowercase - converting string to lowercase.'],
                ['test: Flag Set name "@FAIL" should be all lowercase - converting string to lowercase.'],
                ['test: you passed "@FAIL", Flag Set must adhere to the regular expressions ' .
                    '{/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                    'have a max length of 50 characters. "@FAIL" was discarded.'],
            );

        $this->assertEquals(null, $inputValidator->sanitize('', 'test'));
        $this->assertEquals('a', $inputValidator->sanitize('    A', 'test'));
        $this->assertEquals('a', $inputValidator->sanitize('A', 'test'));
        $this->assertEquals(null, $inputValidator->sanitize('@FAIL', 'test'));
    }

    public function testSanitizeMany()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $inputValidator = new InputValidator($logger);

        $logger
            ->expects($this->exactly(6))
            ->method('warning')
            ->withConsecutive(
                ['test: Flag Set name "   A  " has extra whitespace, trimming.'],
                ['test: Flag Set name "   A  " should be all lowercase - converting string to lowercase.'],
                ['test: Flag Set name "@FAIL" should be all lowercase - converting string to lowercase.'],
                ['test: you passed "@FAIL", Flag Set must adhere to the regular expressions ' .
                    '{/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                    'have a max length of 50 characters. "@FAIL" was discarded.'],
                ['test: Flag Set name "TEST" should be all lowercase - converting string to lowercase.'],
                ['test: Flag Set name "  a" has extra whitespace, trimming.'],
            );
        $logger
            ->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive(
                ['test: FlagSets must be a non-empty list.'],
                ['test: FlagSets must be a non-empty list.']
            );

        $this->assertEquals(['a', 'test'], $inputValidator->sanitizeMany(['   A  ', '@FAIL', 'TEST'], 'test'));
        $this->assertEquals(null, $inputValidator->sanitizeMany([], 'test'));
        $this->assertEquals(null, $inputValidator->sanitizeMany(['some' => 'some'], 'test'));
        $this->assertEquals(['a', 'test'], $inputValidator->sanitizeMany(['a', 'test', '  a'], 'test'));
    }
}
