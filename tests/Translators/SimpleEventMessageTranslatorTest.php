<?php

namespace Tests\Translators;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\Events\SimpleEvent;
use Superbalist\EventPubSub\Translators\SimpleEventMessageTranslator;

class SimpleEventMessageTranslatorTest extends TestCase
{
    public function testToEvent()
    {
        $translator = new SimpleEventMessageTranslator();
        $message = [
            'event' => 'user.created',
            'user' => [
                'id' => 1456,
            ],
        ];
        $event = $translator->translate($message);
        $this->assertInstanceOf(EventInterface::class, $event);
        $this->assertInstanceOf(SimpleEvent::class, $event);

        $this->assertEquals('user.created', $event->getName());
        $this->assertEquals(['user' => ['id' => 1456]], $event->getAttributes());
    }

    public function testToEventWhenMessageIsNotArray()
    {
        $translator = new SimpleEventMessageTranslator();
        $this->assertNull($translator->translate('hello world'));
    }

    public function testToEventWhenEventKeyIsMissing()
    {
        $translator = new SimpleEventMessageTranslator();
        $message = [
            'blah' => 'bleh',
        ];
        $this->assertNull($translator->translate($message));
    }
}
