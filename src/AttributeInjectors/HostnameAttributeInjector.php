<?php

namespace Superbalist\EventPubSub\AttributeInjectors;

use Superbalist\EventPubSub\AttributeInjectorInterface;

class HostnameAttributeInjector implements AttributeInjectorInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @param string $key
     */
    public function __construct($key = 'hostname')
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getAttributeKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getAttributeValue()
    {
        return gethostname();
    }
}
