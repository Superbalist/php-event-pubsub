<?php

namespace Superbalist\EventPubSub\Events;

use Superbalist\EventPubSub\EventInterface;

class SimpleEvent implements EventInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param string $name
     * @param array $attributes
     */
    public function __construct($name, array $attributes = [])
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    /**
     * Return the event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return all event attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Return an event attribute.
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * Check whether or not an event has an attribute.
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Return the event in a message format ready for publishing.
     *
     * @return mixed
     */
    public function toMessage()
    {
        return array_merge($this->attributes, [
            'event' => $this->name,
        ]);
    }

    /**
     * Check whether or not the event matches the given expression.
     *
     * @param string $expr
     * @return bool
     */
    public function matches($expr)
    {
        return $expr === '*' || $this->name === $expr;
    }
}
