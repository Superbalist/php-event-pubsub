<?php

namespace Tests\AttributeInjectors;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\AttributeInjectors\Uuid4AttributeInjector;

class Uuid4AttributeInjectorTest extends TestCase
{
    public function testGetAttributeKey()
    {
        $injector = new Uuid4AttributeInjector();
        $this->assertEquals('uuid', $injector->getAttributeKey());

        $injector = new Uuid4AttributeInjector('custom_attribute');
        $this->assertEquals('custom_attribute', $injector->getAttributeKey());
    }

    public function testGetAttributeValue()
    {
        $injector = new Uuid4AttributeInjector();
        $uuid = $injector->getAttributeValue();
        $match = preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $uuid);
        $this->assertEquals(1, $match);
    }
}
