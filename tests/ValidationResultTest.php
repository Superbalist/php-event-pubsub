<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\EventValidatorInterface;
use Superbalist\EventPubSub\ValidationResult;

class ValidationResultTest extends TestCase
{
    public function testGetValidator()
    {
        $validator = Mockery::mock(EventValidatorInterface::class);
        $event = Mockery::mock(EventInterface::class);
        $result = new ValidationResult($validator, $event, true, []);
        $this->assertSame($validator, $result->getValidator());
    }

    public function testGetEvent()
    {
        $validator = Mockery::mock(EventValidatorInterface::class);
        $event = Mockery::mock(EventInterface::class);
        $result = new ValidationResult($validator, $event, true, []);
        $this->assertSame($event, $result->getEvent());
    }

    public function testPasses()
    {
        $validator = Mockery::mock(EventValidatorInterface::class);
        $event = Mockery::mock(EventInterface::class);
        $result = new ValidationResult($validator, $event, true, []);
        $this->assertTrue($result->passes());

        $result = new ValidationResult($validator, $event, false, ['Required properties missing: ["user"]']);
        $this->assertFalse($result->passes());
    }

    public function testFails()
    {
        $validator = Mockery::mock(EventValidatorInterface::class);
        $event = Mockery::mock(EventInterface::class);
        $result = new ValidationResult($validator, $event, true, []);
        $this->assertFalse($result->fails());

        $result = new ValidationResult($validator, $event, false, ['Required properties missing: ["user"]']);
        $this->assertTrue($result->fails());
    }

    public function testErrors()
    {
        $validator = Mockery::mock(EventValidatorInterface::class);
        $event = Mockery::mock(EventInterface::class);
        $result = new ValidationResult($validator, $event, true, []);
        $this->assertEmpty($result->errors());

        $result = new ValidationResult($validator, $event, false, ['Required properties missing: ["user"]']);
        $this->assertEquals(['Required properties missing: ["user"]'], $result->errors());
    }

    public function testGetErrors()
    {
        $validator = Mockery::mock(EventValidatorInterface::class);
        $event = Mockery::mock(EventInterface::class);
        $result = new ValidationResult($validator, $event, true, []);
        $this->assertEmpty($result->getErrors());

        $result = new ValidationResult($validator, $event, false, ['Required properties missing: ["user"]']);
        $this->assertEquals(['Required properties missing: ["user"]'], $result->getErrors());
    }
}
