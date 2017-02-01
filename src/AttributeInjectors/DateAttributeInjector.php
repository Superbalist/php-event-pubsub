<?php

namespace Superbalist\EventPubSub\AttributeInjectors;

use Superbalist\EventPubSub\AttributeInjectorInterface;

class DateAttributeInjector implements AttributeInjectorInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @param string $key
     */
    public function __construct($key = 'date')
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
        return date('c');
    }
}
