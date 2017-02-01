<?php

namespace Superbalist\EventPubSub;

interface EventInterface
{
    /**
     * Return the event name.
     *
     * @return string
     */
    public function getName();

    /**
     * Return all event attributes.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Return an event attribute.
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name);

    /**
     * Set an event attribute.
     *
     * @param string|array $name
     * @param mixed $value
     */
    public function setAttribute($name, $value = null);

    /**
     * Check whether or not an event has an attribute.
     *
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name);

    /**
     * Check whether or not the event matches the given expression.
     *
     * @param string $expr
     * @return bool
     */
    public function matches($expr);

    /**
     * Return the event in a message format ready for publishing.
     *
     * @return mixed
     */
    public function toMessage();
}
