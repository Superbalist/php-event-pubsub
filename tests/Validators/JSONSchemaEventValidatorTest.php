<?php

namespace Tests\Validators;

use League\JsonGuard\Dereferencer;
use League\JsonGuard\Validator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\Events\SchemaEvent;
use Superbalist\EventPubSub\Events\SimpleEvent;
use Superbalist\EventPubSub\ValidationResult;
use Superbalist\EventPubSub\Validators\JSONSchemaEventValidator;

class JSONSchemaEventValidatorTest extends TestCase
{
    public function testValidate()
    {
        $dereferencer = Mockery::mock(Dereferencer::class);
        $dereferencer->shouldReceive('dereference')
            ->with('http://schemas.my-website.org/events/user/created/1.0.json')
            ->once()
            ->andReturn($this->getMockSchema());

        $validator = new JSONSchemaEventValidator($dereferencer);

        $event = $this->makeTestEvent();

        $result = $validator->validate($event);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertSame($validator, $result->getValidator());
        $this->assertSame($event, $result->getEvent());
        $this->assertTrue($result->passes());
        $this->assertFalse($result->fails());
        $this->assertEmpty($result->getErrors());
    }

    public function testValidateWhenJsonIsInvalid()
    {
        $dereferencer = Mockery::mock(Dereferencer::class);
        $dereferencer->shouldReceive('dereference')
            ->with('http://schemas.my-website.org/events/user/created/1.0.json')
            ->once()
            ->andReturn($this->getMockSchema());

        $validator = new JSONSchemaEventValidator($dereferencer);

        $event = new SchemaEvent(
            'http://schemas.my-website.org/events/user/created/1.0.json',
            [
                'bleh' => 'bleh',
            ]
        );

        $result = $validator->validate($event);
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertSame($validator, $result->getValidator());
        $this->assertSame($event, $result->getEvent());
        $this->assertFalse($result->passes());
        $this->assertTrue($result->fails());
        $this->assertEquals(
            ['Required properties missing: ["user"]'],
            $result->errors()
        );
    }

    public function testMakeSchemaValidator()
    {
        $dereferencer = Mockery::mock(Dereferencer::class);

        $validator = new JSONSchemaEventValidator($dereferencer);

        $event = $this->makeTestEvent();

        $schemaValidator = $validator->makeSchemaValidator($event, $this->getMockSchema());

        $this->assertInstanceOf(Validator::class, $schemaValidator);
    }

    public function testGetEventSchema()
    {
        $schema = $this->getMockSchema();

        $dereferencer = Mockery::mock(Dereferencer::class);
        $dereferencer->shouldReceive('dereference')
            ->with('http://schemas.my-website.org/events/user/created/1.0.json')
            ->once()
            ->andReturn($schema);

        $validator = new JSONSchemaEventValidator($dereferencer);

        $event = $this->makeTestEvent();

        $this->assertEquals($schema, $validator->getEventSchema($event));
    }

    public function testGetEventSchemaOnNonSchemaEventObject()
    {
        $dereferencer = Mockery::mock(Dereferencer::class);

        $validator = new JSONSchemaEventValidator($dereferencer);

        $event = new SimpleEvent('user.created');

        $this->assertNull($validator->getEventSchema($event));
    }

    /**
     * @return SchemaEvent
     */
    protected function makeTestEvent()
    {
        return new SchemaEvent(
            'http://schemas.my-website.org/events/user/created/1.0.json',
            [
                'user' => [
                    'id' => 1456,
                    'first_name' => 'Joe',
                    'last_name' => 'Soap',
                    'email' => 'joe.soap@example.org',
                ],
                'request' => [
                    'ip' => '127.0.0.1',
                    'useragent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
                    'url' => 'http://my.website.example.com',
                ],
            ]
        );
    }

    /**
     * @return object
     */
    protected function getMockSchema()
    {
        return json_decode(json_encode([
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'Test Schema',
            'type' => 'object',
            'properties' => [
                'schema' => [
                    'type' => 'string',
                ],
                'user' => [
                    'type' => 'object',
                ],
            ],
            'required' => [
                'schema',
                'user',
            ],
        ]));
    }
}
