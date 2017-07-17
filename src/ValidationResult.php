<?php

namespace Superbalist\EventPubSub;

class ValidationResult
{
    /**
     * @var EventValidatorInterface
     */
    protected $validator;

    /**
     * @var EventInterface
     */
    protected $event;

    /**
     * @var bool
     */
    protected $passes;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @param EventValidatorInterface $validator
     * @param EventInterface $event
     * @param bool $passes
     * @param array $errors
     */
    public function __construct(EventValidatorInterface $validator, EventInterface $event, $passes, array $errors = [])
    {
        $this->validator = $validator;
        $this->event = $event;
        $this->passes = $passes;
        $this->errors = $errors;
    }

    /**
     * @return EventValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @return EventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return bool
     */
    public function passes()
    {
        return $this->passes;
    }

    /**
     * @return bool
     */
    public function fails()
    {
        return !$this->passes;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors();
    }
}
