<?php

namespace Superbalist\EventPubSub\Translators;

use Superbalist\EventPubSub\Events\SimpleEvent;
use Superbalist\EventPubSub\MessageTranslatorInterface;

class SimpleEventMessageTranslator implements MessageTranslatorInterface
{
    /**
     * @param mixed $message
     *
     * @return null|SimpleEvent
     */
    public function translate($message)
    {
        // message must be an array
        if (!is_array($message)) {
            return null;
        }

        // we must have an event property
        if (!isset($message['event'])) {
            return null;
        }

        // don't include the event name as an attribute
        $attributes = $message;
        unset($attributes['event']);

        return new SimpleEvent($message['event'], $attributes);
    }
}
