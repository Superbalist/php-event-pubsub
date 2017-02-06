<?php

namespace Superbalist\EventPubSub\Translators;

use Superbalist\EventPubSub\Events\SchemaEvent;
use Superbalist\EventPubSub\MessageTranslatorInterface;

class SchemaEventMessageTranslator implements MessageTranslatorInterface
{
    /**
     * @param mixed $message
     *
     * @return null|SchemaEvent
     */
    public function translate($message)
    {
        // message must be an array
        if (!is_array($message)) {
            return null;
        }

        // we must have a schema property
        if (!isset($message['schema'])) {
            return null;
        }

        // don't include the schema as an attribute
        $attributes = $message;
        unset($attributes['schema']);

        return new SchemaEvent($message['schema'], $attributes);
    }

    /**
     * @param string $str
     *
     * @return array|null
     */
    public static function parseSchemaStr($str)
    {
        // schema must match the regular expression '(protocol)://(......)?/events/(topic)/(channel)/(version).json'
        // eg: http://schemas.my-website.org/events/user/created/1.0.json
        if (!preg_match('#^(.+)://(.+/)?events/(.+)/(.+)/(.+)\.json$#', $str, $matches)) {
            return null;
        }

        return [
            'topic' => $matches[3],
            'event' => $matches[4],
            'version' => $matches[5],
        ];
    }
}
