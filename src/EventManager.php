<?php

namespace Superbalist\EventPubSub;

use Superbalist\PubSub\PubSubAdapterInterface;

class EventManager
{
    /**
     * @var PubSubAdapterInterface
     */
    protected $adapter;

    /**
     * @var MessageTranslatorInterface
     */
    protected $translator;

    /**
     * @var EventValidatorInterface|null
     */
    protected $validator;

    /**
     * @param PubSubAdapterInterface $adapter
     * @param MessageTranslatorInterface $translator
     * @param EventValidatorInterface|null $validator
     */
    public function __construct(
        PubSubAdapterInterface $adapter,
        MessageTranslatorInterface $translator,
        EventValidatorInterface $validator = null
    ) {
        $this->adapter = $adapter;
        $this->translator = $translator;
        $this->validator = $validator;
    }

    /**
     * Return the underlying pub/sub adapter.
     *
     * @return PubSubAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Return the event translator.
     *
     * @return MessageTranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Return the event validator.
     *
     * @return EventValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Listen for an event.
     *
     * @param string $channel
     * @param string $expr
     * @param callable $handler
     */
    public function listen($channel, $expr, callable $handler)
    {
        $this->adapter->subscribe($channel, function ($message) use ($expr, $handler) {
            $event = $this->translator->translate($message);

            if ($event) {
                // we were able to translate the message into an event
                if ($event->matches($expr)) {
                    // the event matches the listen expression
                    if ($this->validator === null || $this->validator->validates($event)) {
                        // event passed validation
                        call_user_func($handler, $event);
                    }
                }
            }
        });
    }

    /**
     * Dispatch an event.
     *
     * @param string $channel
     * @param EventInterface $event
     */
    public function dispatch($channel, EventInterface $event)
    {
        $this->adapter->publish($channel, $event->toMessage());
    }
}
