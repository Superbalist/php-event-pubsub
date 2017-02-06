<?php

namespace Superbalist\EventPubSub\Validators;

use League\JsonGuard\Dereferencer;
use League\JsonGuard\Validator;
use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\Events\SchemaEvent;
use Superbalist\EventPubSub\EventValidatorInterface;

class JSONSchemaEventValidator implements EventValidatorInterface
{
    /**
     * @var Dereferencer
     */
    protected $dereferencer;

    /**
     * @param Dereferencer $dereferencer
     */
    public function __construct(Dereferencer $dereferencer)
    {
        $this->dereferencer = $dereferencer;
    }

    /**
     * @param EventInterface $event
     *
     * @return bool
     */
    public function validates(EventInterface $event)
    {
        $schema = $this->getEventSchema($event);
        if ($schema === null) {
            return true;
        }
        return $this->isValidAgainstSchema($event, $schema);
    }

    /**
     * @param EventInterface $event
     * @param object $schema
     *
     * @return bool
     */
    public function isValidAgainstSchema(EventInterface $event, $schema)
    {
        // we can't validate on an array, only an object
        // so we need to convert the event payload to an object
        $payload = $event->toMessage();
        $payload = json_encode($payload); // back to json
        $payload = json_decode($payload); // from json to an object

        $validator = new Validator($payload, $schema);

        return $validator->passes();
    }

    /**
     * @param EventInterface $event
     *
     * @return null|object
     */
    public function getEventSchema(EventInterface $event)
    {
        if (!$event instanceof SchemaEvent) {
            return null;
        }

        return $this->dereferencer->dereference($event->getSchema());
    }
}
