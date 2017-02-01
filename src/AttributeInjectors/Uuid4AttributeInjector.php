<?php

namespace Superbalist\EventPubSub\AttributeInjectors;

use Ramsey\Uuid\Uuid;
use Superbalist\EventPubSub\AttributeInjectorInterface;

class Uuid4AttributeInjector implements AttributeInjectorInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @param string $key
     */
    public function __construct($key = 'uuid')
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
        return Uuid::uuid4()->toString();
    }
}
