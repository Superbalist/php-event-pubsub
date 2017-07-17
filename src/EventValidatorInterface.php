<?php

namespace Superbalist\EventPubSub;

interface EventValidatorInterface
{
    /**
     * @param EventInterface $event
     *
     * @return ValidationResult
     */
    public function validate(EventInterface $event);
}
