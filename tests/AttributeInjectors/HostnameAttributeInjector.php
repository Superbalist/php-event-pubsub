<?php

namespace Tests\AttributeInjectors;

use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\AttributeInjectors\HostnameAttributeInjector;

class HostnameAttributeInjector extends TestCase
{
    public function testGetAttributeKey()
    {
        $injector = new HostnameAttributeInjector();
        $this->assertEquals('hostname', $injector->getAttributeKey());

        $injector = new HostnameAttributeInjector('custom_attribute');
        $this->assertEquals('custom_attribute', $injector->getAttributeKey());
    }

    public function testGetAttributeValue()
    {
        $injector = new HostnameAttributeInjector();
        $this->assertEquals(gethostname(), $injector->getAttributeValue());
    }
}
