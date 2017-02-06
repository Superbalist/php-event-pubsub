<?php

namespace Tests\Events;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\Events\SimpleEvent;

class SimpleEventTest extends TestCase
{
    public function testGetName()
    {
        $event = $this->makeTestEvent();
        $this->assertEquals('user.created', $event->getName());
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
                'useragent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
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
            'event' => 'user.created',
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
        ];
        $this->assertEquals($expected, $event->toMessage());
    }

    public function testMatches()
    {
        $event = $this->makeTestEvent();
        $this->assertTrue($event->matches('*'));
        $this->assertTrue($event->matches('user.created'));
        $this->assertFalse($event->matches('user.updated'));
        $this->assertFalse($event->matches('User.Created'));
    }

    /**
     * @return SimpleEvent
     */
    protected function makeTestEvent()
    {
        return new SimpleEvent(
            'user.created',
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
}
