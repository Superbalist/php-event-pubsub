<?php

namespace Tests\Events;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\Events\TopicEvent;

class TopicEventTest extends TestCase
{
    public function testGetTopic()
    {
        $event = $this->makeTestEvent();
        $this->assertEquals('user', $event->getTopic());
    }

    public function testGetName()
    {
        $event = $this->makeTestEvent();
        $this->assertEquals('created', $event->getName());
    }

    public function testGetVersion()
    {
        $event = $this->makeTestEvent();
        $this->assertEquals('1.0', $event->getVersion());
    }

    public function testGetAttributes()
    {
        $expected = [
            'user' => [
                'id' => 1456,
                'first_name' => 'Joe',
                'last_name' => 'Soap',
                'email' => 'joe.soap@example.org',
            ],
            'request' => [
                'ip' => '127.0.0.1',
                'useragent'=> 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
                'url' => 'http://my.website.example.com',
            ],
        ];
        $event = $this->makeTestEvent();
        $this->assertEquals($expected, $event->getAttributes());
    }

    public function testGetAttribute()
    {
        $expected = [
            'id' => 1456,
            'first_name' => 'Joe',
            'last_name' => 'Soap',
            'email' => 'joe.soap@example.org',
        ];
        $event = $this->makeTestEvent();
        $this->assertEquals($expected, $event->getAttribute('user'));

        $this->assertNull($event->getAttribute('bogus'));
    }

    public function testHasAttribute()
    {
        $event = $this->makeTestEvent();
        $this->assertTrue($event->hasAttribute('user'));
        $this->assertTrue($event->hasAttribute('request'));
        $this->assertFalse($event->hasAttribute('bogus'));
    }

    public function testToMessage()
    {
        $event = $this->makeTestEvent();
        $expected = [
            'topic' => 'user',
            'event' => 'created',
            'version' => '1.0',
            'user' => [
                'id' => 1456,
                'first_name' => 'Joe',
                'last_name' => 'Soap',
                'email' => 'joe.soap@example.org',
            ],
            'request' => [
                'ip' => '127.0.0.1',
                'useragent'=> 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
                'url' => 'http://my.website.example.com',
            ],
        ];
        $this->assertEquals($expected, $event->toMessage());
    }

    public function testMatches()
    {
        $event = $this->makeTestEvent();
        $this->assertTrue($event->matches('*'));
        $this->assertTrue($event->matches('user'));
        $this->assertTrue($event->matches('user/created'));
        $this->assertTrue($event->matches('user/created/1.0'));
        $this->assertTrue($event->matches('user/*'));
        $this->assertTrue($event->matches('user/created/*'));
        $this->assertFalse($event->matches('order'));
        $this->assertFalse($event->matches('order/created'));
        $this->assertFalse($event->matches('order/created/1.0'));
        $this->assertFalse($event->matches('order/*'));
        $this->assertFalse($event->matches('order/created/*'));
        $this->assertFalse($event->matches('user/updated'));
        $this->assertFalse($event->matches('user/updated/*'));
        $this->assertFalse($event->matches('user/updated/1.0'));
    }

    public function testParseEventExpr()
    {
        $this->assertEquals(
            ['topic' => '*', 'event' => '*', 'version' => '*'],
            TopicEvent::parseEventExpr('*')
        );

        $this->assertEquals(
            ['topic' => 'user', 'event' => '*', 'version' => '*'],
            TopicEvent::parseEventExpr('user')
        );

        $this->assertEquals(
            ['topic' => 'user', 'event' => 'created', 'version' => '*'],
            TopicEvent::parseEventExpr('user/created')
        );

        $this->assertEquals(
            ['topic' => 'user', 'event' => 'created', 'version' => '1.0'],
            TopicEvent::parseEventExpr('user/created/1.0')
        );

        $this->assertEquals(
            ['topic' => 'user', 'event' => '*', 'version' => '*'],
            TopicEvent::parseEventExpr('user/*')
        );

        $this->assertEquals(
            ['topic' => 'user', 'event' => 'created', 'version' => '*'],
            TopicEvent::parseEventExpr('user/created/*')
        );

        $this->assertEquals(
            ['topic' => '*', 'event' => '*', 'version' => '*'],
            TopicEvent::parseEventExpr('*/created')
        );

        $this->assertEquals(
            ['topic' => '*', 'event' => '*', 'version' => '*'],
            TopicEvent::parseEventExpr('*/created/*')
        );

        $this->assertEquals(
            ['topic' => 'user', 'event' => '*', 'version' => '*'],
            TopicEvent::parseEventExpr('user/*/1.0')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseEventExprWhenExprIsInvalid()
    {
        TopicEvent::parseEventExpr('*/');
    }

    /**
     * @return TopicEvent
     */
    protected function makeTestEvent()
    {
        return new TopicEvent(
            'user',
            'created',
            '1.0',
            [
                'user' => [
                    'id' => 1456,
                    'first_name' => 'Joe',
                    'last_name' => 'Soap',
                    'email' => 'joe.soap@example.org',
                ],
                'request' => [
                    'ip' => '127.0.0.1',
                    'useragent'=> 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
                    'url' => 'http://my.website.example.com',
                ],
            ]
        );
    }
}
