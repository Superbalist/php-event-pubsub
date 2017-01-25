<?php

namespace Superbalist\EventPubSub;

interface MessageTranslatorInterface
{
    /**
     * @param mixed $message
     * @return null|EventInterface
     */
    public function translate($message);
}
