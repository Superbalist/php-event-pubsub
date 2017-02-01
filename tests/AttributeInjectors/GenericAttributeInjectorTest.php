<?php

namespace Tests\AttributeInjectors;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\AttributeInjectors\GenericAttributeInjector;

class GenericAttributeInjectorTest extends TestCase
{
    public function testGetAttributeKey()
    {
        $injector = new GenericAttributeInjector('key', 'value');
        $this->assertEquals('key', $injector->getAttributeKey());
    }

    public function testGetAttributeValue()
    {
        $injector = new GenericAttributeInjector('key', 'value');
        $this->assertEquals('value', $injector->getAttributeValue());
    }
}
