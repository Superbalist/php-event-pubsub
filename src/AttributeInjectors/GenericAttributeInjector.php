<?php

namespace Superbalist\EventPubSub\AttributeInjectors;

use Superbalist\EventPubSub\AttributeInjectorInterface;

class GenericAttributeInjector implements AttributeInjectorInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
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
        return $this->value;
    }
}
