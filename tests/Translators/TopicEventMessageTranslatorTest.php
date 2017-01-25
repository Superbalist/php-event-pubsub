<?php

namespace Tests\Translators;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\Events\TopicEvent;
use Superbalist\EventPubSub\Translators\TopicEventMessageTranslator;

class TopicEventMessageTranslatorTest extends TestCase
{
    public function testToEvent()
    {
        $translator = new TopicEventMessageTranslator();
        $message = [
            'topic' => 'user',
            'event' => 'created',
            'version' => '1.0',
            'user' => [
                'id' => 1456,
            ],
        ];
        $event = $translator->translate($message);
        $this->assertInstanceOf(EventInterface::class, $event);
        $this->assertInstanceOf(TopicEvent::class, $event);

        $this->assertEquals('user', $event->getTopic());
        $this->assertEquals('created', $event->getName());
        $this->assertEquals('1.0', $event->getVersion());
        $this->assertEquals(['user' => ['id' => 1456]], $event->getAttributes());
    }

    public function testToEventWhenMessageIsNotArray()
    {
        $translator = new TopicEventMessageTranslator();
        $this->assertNull($translator->translate('hello world'));
    }

    public function testToEventWhenTopicKeyIsMissing()
    {
        $translator = new TopicEventMessageTranslator();
        $message = [
            'event' => 'created',
            'version' => '1.0',
        ];
        $this->assertNull($translator->translate($message));
    }

    public function testToEventWhenEventKeyIsMissing()
    {
        $translator = new TopicEventMessageTranslator();
        $message = [
            'topic' => 'user',
            'version' => '1.0',
        ];
        $this->assertNull($translator->translate($message));
    }

    public function testToEventWhenVersionKeyIsMissing()
    {
        $translator = new TopicEventMessageTranslator();
        $message = [
            'topic' => 'user',
            'event' => 'created',
        ];
        $this->assertNull($translator->translate($message));
    }
}
