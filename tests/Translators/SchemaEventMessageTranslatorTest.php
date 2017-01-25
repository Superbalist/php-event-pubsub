<?php

namespace Tests\Translators;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\Events\SchemaEvent;
use Superbalist\EventPubSub\Translators\SchemaEventMessageTranslator;

class SchemaEventMessageTranslatorTest extends TestCase
{
    public function testToEvent()
    {
        $translator = new SchemaEventMessageTranslator();
        $message = [
            'schema' => 'http://schemas.my-website.org/events/user/created/1.0.json',
            'user' => [
                'id' => 1456,
            ],
        ];
        $event = $translator->translate($message);
        $this->assertInstanceOf(EventInterface::class, $event);
        $this->assertInstanceOf(SchemaEvent::class, $event);

        $this->assertEquals('user', $event->getTopic());
        $this->assertEquals('created', $event->getName());
        $this->assertEquals('1.0', $event->getVersion());
        $this->assertEquals(['user' => ['id' => 1456]], $event->getAttributes());
    }

    public function testToEventWhenMessageIsNotArray()
    {
        $translator = new SchemaEventMessageTranslator();
        $this->assertNull($translator->translate('hello world'));
    }

    public function testToEventWhenSchemaKeyIsMissing()
    {
        $translator = new SchemaEventMessageTranslator();
        $message = [
            'blah' => 'bleh',
        ];
        $this->assertNull($translator->translate($message));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testToEventWhenSchemaIsInvalid()
    {
        $translator = new SchemaEventMessageTranslator();
        $message = [
            'schema' => 'hello world',
        ];
        $translator->translate($message);
    }

    public function testParseSchemaStr()
    {
        $this->assertNull(SchemaEventMessageTranslator::parseSchemaStr('bleh'));

        $schema = SchemaEventMessageTranslator::parseSchemaStr('array://events/user/created/1.0.json');
        $this->assertInternalType('array', $schema);
        $this->assertArrayHasKey('topic', $schema);
        $this->assertArrayHasKey('event', $schema);
        $this->assertArrayHasKey('version', $schema);
        $this->assertEquals('user', $schema['topic']);
        $this->assertEquals('created', $schema['event']);
        $this->assertEquals('1.0', $schema['version']);

        $schema = SchemaEventMessageTranslator::parseSchemaStr('http://schema.my-website.com/events/user/created/1.0.json');
        $this->assertInternalType('array', $schema);
        $this->assertArrayHasKey('topic', $schema);
        $this->assertArrayHasKey('event', $schema);
        $this->assertArrayHasKey('version', $schema);
        $this->assertEquals('user', $schema['topic']);
        $this->assertEquals('created', $schema['event']);
        $this->assertEquals('1.0', $schema['version']);
    }
}
