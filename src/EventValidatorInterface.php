<?php

namespace Superbalist\EventPubSub;

interface EventValidatorInterface
{
    /**
     * @param EventInterface $event
     * @return bool
     */
    public function validates(EventInterface $event);
}
