<?php

namespace Tests\AttributeInjectors;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\AttributeInjectors\HostnameAttributeInjector;

class HostnameAttributeInjectorTest extends TestCase
{
    public function testGetAttributeKey()
    {
        $injector = new HostnameAttributeInjectorTest();
        $this->assertEquals('hostname', $injector->getAttributeKey());

        $injector = new HostnameAttributeInjectorTest('custom_attribute');
        $this->assertEquals('custom_attribute', $injector->getAttributeKey());
    }

    public function testGetAttributeValue()
    {
        $injector = new HostnameAttributeInjectorTest();
        $this->assertEquals(gethostname(), $injector->getAttributeValue());
    }
}
