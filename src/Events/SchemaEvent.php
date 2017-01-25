<?php

namespace Superbalist\EventPubSub\Events;

use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\Translators\SchemaEventMessageTranslator;

class SchemaEvent extends TopicEvent implements EventInterface
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @param string $schema
     * @param array $attributes
     */
    public function __construct($schema, array $attributes = [])
    {
        $params = SchemaEventMessageTranslator::parseSchemaStr($schema);
        if ($params === null) {
            throw new \InvalidArgumentException(
                'The schema string must be in the format "(protocol)://(......)?/events/(topic)/(channel)/(version).json".'
            );
        }

        parent::__construct($params['topic'], $params['event'], $params['version'], $attributes);

        $this->schema = $schema;
    }

    /**
     * Return the event schema.
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Return the event in a message format ready for publishing.
     *
     * @return mixed
     */
    public function toMessage()
    {
        return array_merge($this->attributes, [
            'schema' => $this->schema,
        ]);
    }
}
