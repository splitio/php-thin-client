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

        $invocations = $this->exactly(6);
        $logger
            ->expects($invocations)
            ->method('warning')
            ->willReturnCallback(fn($arg) => match ([$invocations->numberOfInvocations(), $arg]) {
                [1, 'test: you passed "", Flag Set must adhere to the regular expressions ' .
                    '{/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                    'have a max length of 50 characters. "" was discarded.'] => null,

                [2, 'test: Flag Set name "    A" has extra whitespace, trimming.'] => null,
                [3, 'test: Flag Set name "    A" should be all lowercase - converting string to lowercase.'] => null,
                [4, 'test: Flag Set name "A" should be all lowercase - converting string to lowercase.'] => null,
                [5, 'test: Flag Set name "@FAIL" should be all lowercase - converting string to lowercase.'] => null,
                [6, 'test: you passed "@FAIL", Flag Set must adhere to the regular expressions ' .
                    '{/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                    'have a max length of 50 characters. "@FAIL" was discarded.'] => null,
            });

        $this->assertEquals(null, $inputValidator->sanitize('', 'test'));
        $this->assertEquals('a', $inputValidator->sanitize('    A', 'test'));
        $this->assertEquals('a', $inputValidator->sanitize('A', 'test'));
        $this->assertEquals(null, $inputValidator->sanitize('@FAIL', 'test'));
    }

    public function testSanitizeMany()
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $inputValidator = new InputValidator($logger);

        $warnInvocations = $this->exactly(6);
        $logger
            ->expects($warnInvocations)
            ->method('warning')
            ->willReturnCallback(fn($arg) => match ([$warnInvocations->numberOfInvocations(), $arg]) {
                [1, 'test: Flag Set name "   A  " has extra whitespace, trimming.'] => null,
                [2, 'test: Flag Set name "   A  " should be all lowercase - converting string to lowercase.'] => null,
                [3, 'test: Flag Set name "@FAIL" should be all lowercase - converting string to lowercase.'] => null,
                [4, 'test: you passed "@FAIL", Flag Set must adhere to the regular expressions ' .
                    '{/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                    'have a max length of 50 characters. "@FAIL" was discarded.'] => null,
                [5, 'test: Flag Set name "TEST" should be all lowercase - converting string to lowercase.'] => null,
                [6, 'test: Flag Set name "  a" has extra whitespace, trimming.'] => null,
            });

        $errorInvocations = $this->exactly(2);
        $logger
            ->expects($errorInvocations)
            ->method('error')
            ->willReturnCallback(fn($arg) => match ([$errorInvocations->numberOfInvocations(), $arg]) {
                [1, 'test: FlagSets must be a non-empty list.'] => null,
                [2, 'test: FlagSets must be a non-empty list.'] => null
            });

        $this->assertEquals(['a', 'test'], $inputValidator->sanitizeMany(['   A  ', '@FAIL', 'TEST'], 'test'));
        $this->assertEquals(null, $inputValidator->sanitizeMany([], 'test'));
        $this->assertEquals(null, $inputValidator->sanitizeMany(['some' => 'some'], 'test'));
        $this->assertEquals(['a', 'test'], $inputValidator->sanitizeMany(['a', 'test', '  a'], 'test'));
    }
}
