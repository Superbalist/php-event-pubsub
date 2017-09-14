<?php

namespace Superbalist\EventPubSub\Validators;

use League\JsonGuard\Dereferencer;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;
use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\Events\SchemaEvent;
use Superbalist\EventPubSub\EventValidatorInterface;
use Superbalist\EventPubSub\ValidationResult;

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
     * @return ValidationResult
     */
    public function validate(EventInterface $event)
    {
        $schema = $this->getEventSchema($event);
        if ($schema === null) {
            return new ValidationResult($this, $event, true);
        }

        $schemaValidator = $this->makeSchemaValidator($event, $schema);
        if ($schemaValidator->passes()) {
            return new ValidationResult($this, $event, true);
        } else {
            $errors = $schemaValidator->errors();
            return new ValidationResult($this, $event, false, $errors);
        }
    }

    /**
     * @param EventInterface $event
     * @param object $schema
     *
     * @return Validator
     */
    public function makeSchemaValidator(EventInterface $event, $schema)
    {
        // we can't validate on an array, only an object
        // so we need to convert the event payload to an object
        $payload = $event->toMessage();
        $payload = json_encode($payload); // back to json
        $payload = json_decode($payload); // from json to an object

        return new Validator($payload, $schema);
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
