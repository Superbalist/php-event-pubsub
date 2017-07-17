<?php

namespace Tests;

use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\ValidationException;
use Superbalist\EventPubSub\ValidationResult;

class ValidationExceptionTest extends TestCase
{
    public function testIsInstanceOfException()
    {
        $validationResult = Mockery::mock(ValidationResult::class);
        $e = new ValidationException($validationResult);
        $this->assertInstanceOf(Exception::class, $e);
    }

    public function testGetValidationResult()
    {
        $validationResult = Mockery::mock(ValidationResult::class);
        $e = new ValidationException($validationResult);
        $this->assertSame($validationResult, $e->getValidationResult());
    }

    public function testDefaultExceptionMessage()
    {
        $validationResult = Mockery::mock(ValidationResult::class);
        $e = new ValidationException($validationResult);
        $this->assertEquals('The event failed validation.', $e->getMessage());
    }

    public function testDefaultExceptionCode()
    {
        $validationResult = Mockery::mock(ValidationResult::class);
        $e = new ValidationException($validationResult);
        $this->assertEquals(0, $e->getCode());
    }
}
