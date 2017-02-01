<?php

namespace Superbalist\EventPubSub;

interface AttributeInjectorInterface
{
    /**
     * @return string
     */
    public function getAttributeKey();

    /**
     * @return mixed
     */
    public function getAttributeValue();
}