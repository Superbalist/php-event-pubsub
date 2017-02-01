<?php

namespace Tests\AttributeInjectors;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\AttributeInjectors\DateAttributeInjector;

class DateAttributeInjectorTest extends TestCase
{
    public function testGetAttributeKey()
    {
        $injector = new DateAttributeInjector();
        $this->assertEquals('date', $injector->getAttributeKey());

        $injector = new DateAttributeInjector('custom_attribute');
        $this->assertEquals('custom_attribute', $injector->getAttributeKey());
    }

    public function testGetAttributeValue()
    {
        $injector = new DateAttributeInjector();
        $this->assertEquals(date('c'), $injector->getAttributeValue());
    }
}
