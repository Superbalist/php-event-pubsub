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
     * @var array
     */
    protected $attributeInjectors;

    /**
     * @param PubSubAdapterInterface $adapter
     * @param MessageTranslatorInterface $translator
     * @param EventValidatorInterface|null $validator
     * @param array $attributeInjectors
     */
    public function __construct(
        PubSubAdapterInterface $adapter,
        MessageTranslatorInterface $translator,
        EventValidatorInterface $validator = null,
        array $attributeInjectors = []
    ) {
        $this->adapter = $adapter;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->attributeInjectors = $attributeInjectors;
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
     * Return all attribute injectors.
     *
     * @return array
     */
    public function getAttributeInjectors()
    {
        return $this->attributeInjectors;
    }

    /**
     * Add an attribute injector.
     *
     * @param AttributeInjectorInterface|callable $attributeInjector
     */
    public function addAttributeInjector($attributeInjector)
    {
        $this->attributeInjectors[] = $attributeInjector;
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
     * @return array
     */
    protected function getValuesFromAttributeInjectors()
    {
        $values = [];

        foreach ($this->attributeInjectors as $injector) {
            if ($injector instanceof AttributeInjectorInterface) {
                $values[$injector->getAttributeKey()] = $injector->getAttributeValue();
            } elseif (is_callable($injector)) {
                $v = call_user_func($injector);
                $values[$v['key']] = $v['value'];
            }
        }

        return $values;
    }

    /**
     * Dispatch an event.
     *
     * @param string $channel
     * @param EventInterface $event
     */
    public function dispatch($channel, EventInterface $event)
    {
        // automagically inject attributes from injectors
        $attributes = $this->getValuesFromAttributeInjectors();
        $e = clone $event; // we don't want to manipulate the original event
        foreach ($attributes as $k => $v) {
            if (!$e->hasAttribute($k)) {
                $e->setAttribute($k, $v);
            }
        }

        $this->adapter->publish($channel, $e->toMessage());
    }
}
