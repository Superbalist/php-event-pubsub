<?php

namespace Superbalist\EventPubSub\Translators;

use Superbalist\EventPubSub\Events\TopicEvent;
use Superbalist\EventPubSub\MessageTranslatorInterface;

class TopicEventMessageTranslator implements MessageTranslatorInterface
{
    /**
     * @param mixed $message
     * @return null|TopicEvent
     */
    public function translate($message)
    {
        // message must be an array
        if (!is_array($message)) {
            return null;
        }

        // the following keys are required to construct a topic event object
        $keys = ['topic', 'event', 'version'];
        foreach ($keys as $k) {
            if (!isset($message[$k])) {
                return null;
            }
        }

        // don't include the topic, event and version as attributes
        $attributes = $message;
        unset($attributes['topic'], $attributes['event'], $attributes['version']);

        return new TopicEvent($message['topic'], $message['event'], $message['version'], $attributes);
    }
}
