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
     * @var callable|null
     */
    protected $translateFailHandler;

    /**
     * @var callable|null
     */
    protected $listenExprFailHandler;

    /**
     * @var callable|null
     */
    protected $validationFailHandler;

    /**
     * @var bool
     */
    protected $throwValidationExceptionsOnDispatch = true;

    /**
     * @param PubSubAdapterInterface $adapter
     * @param MessageTranslatorInterface $translator
     * @param EventValidatorInterface|null $validator
     * @param array $attributeInjectors
     * @param callable|null $translateFailHandler
     * @param callable|null $listenExprFailHandler
     * @param callable|null $validationFailHandler
     */
    public function __construct(
        PubSubAdapterInterface $adapter,
        MessageTranslatorInterface $translator,
        EventValidatorInterface $validator = null,
        array $attributeInjectors = [],
        callable $translateFailHandler = null,
        callable $listenExprFailHandler = null,
        callable $validationFailHandler = null
    ) {
        $this->adapter = $adapter;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->attributeInjectors = $attributeInjectors;
        $this->translateFailHandler = $translateFailHandler;
        $this->listenExprFailHandler = $listenExprFailHandler;
        $this->validationFailHandler = $validationFailHandler;
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
     * Set the handler to be called when a message is received but fails to translate to an event.
     *
     * @param callable $handler
     */
    public function setTranslateFailHandler(callable $handler)
    {
        $this->translateFailHandler = $handler;
    }

    /**
     * Return the handler which is called when a message is received but fails to translate to an event.
     *
     * @return callable|null
     */
    public function getTranslateFailHandler()
    {
        return $this->translateFailHandler;
    }

    /**
     * Set the handler which is called when an event is received but doesn't match the listen expression.
     *
     * @param callable $handler
     */
    public function setListenExprFailHandler(callable $handler)
    {
        $this->listenExprFailHandler = $handler;
    }

    /**
     * Return the handler which is called when an event is received but doesn't match the listen expression.
     *
     * @return callable|null
     */
    public function getListenExprFailHandler()
    {
        return $this->listenExprFailHandler;
    }

    /**
     * Set the handler which is called when an event is dispatched or received but fails validation.
     *
     * @param callable $handler
     */
    public function setValidationFailHandler(callable $handler)
    {
        $this->validationFailHandler = $handler;
    }

    /**
     * Return the handler which is called when an event is dispatched or received but fails validation.
     *
     * @return callable|null
     */
    public function getValidationFailHandler()
    {
        return $this->validationFailHandler;
    }

    /**
     * @param bool|null $bool
     * @return mixed
     */
    public function throwValidationExceptionsOnDispatch($bool = null)
    {
        if ($bool === null) {
            return $this->throwValidationExceptionsOnDispatch;
        } else {
            $this->throwValidationExceptionsOnDispatch = $bool;
        }
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
            $this->handleSubscribeCallback($message, $expr, $handler);
        });
    }

    /**
     * @param mixed $message
     * @param string $expr
     * @param callable $handler
     *
     * @internal
     */
    public function handleSubscribeCallback($message, $expr, callable $handler)
    {
        $event = $this->translator->translate($message);
        if ($event) {
            // we were able to translate the message into an event
            if ($event->matches($expr)) {
                // the event matches the listen expression
                if ($this->validator === null) {
                    // nothing to validate
                    call_user_func($handler, $event);
                } else {
                    $result = $this->validator->validate($event);
                    if ($result->passes()) {
                        // event validates!
                        call_user_func($handler, $event);
                    } else {
                        // pass to validation fail handler?
                        if ($this->validationFailHandler) {
                            call_user_func($this->validationFailHandler, $result);
                        }
                    }
                }
            } else {
                // pass to listen expr fail handler?
                if ($this->listenExprFailHandler) {
                    call_user_func($this->listenExprFailHandler, $event, $expr);
                }
            }
        } else {
            // pass to translate fail handler?
            if ($this->translateFailHandler) {
                call_user_func($this->translateFailHandler, $message);
            }
        }
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
     * @param EventInterface $event
     *
     * @return EventInterface
     */
    protected function prepEventForDispatch(EventInterface $event)
    {
        // automagically inject attributes from injectors
        $attributes = $this->getValuesFromAttributeInjectors();
        $e = clone $event; // we don't want to manipulate the original event
        foreach ($attributes as $k => $v) {
            if (!$e->hasAttribute($k)) {
                $e->setAttribute($k, $v);
            }
        }
        return $e;
    }

    /**
     * Dispatch an event.
     *
     * @param string $channel
     * @param EventInterface $event
     * @throws ValidationException
     */
    public function dispatch($channel, EventInterface $event)
    {
        $e = $this->prepEventForDispatch($event);
        if ($this->validator) {
            $result = $this->validator->validate($event);
            if ($result->fails()) {
                // pass to validation fail handler?
                if ($this->validationFailHandler) {
                    call_user_func($this->validationFailHandler, $result);
                }

                if ($this->throwValidationExceptionsOnDispatch) {
                    throw new ValidationException($result);
                }
            }
        }

        $this->adapter->publish($channel, $e->toMessage());
    }

    /**
     * Dispatch multiple events.
     *
     * @param string $channel
     * @param array $events
     * @throws ValidationException
     */
    public function dispatchBatch($channel, array $events)
    {
        $messages = [];

        foreach ($events as $event) {
            /** @var EventInterface $event */
            $event = $this->prepEventForDispatch($event);

            if ($this->validator) {
                $result = $this->validator->validate($event);
                if ($result->fails()) {
                    // pass to validation fail handler?
                    if ($this->validationFailHandler) {
                        call_user_func($this->validationFailHandler, $result);
                    }

                    if ($this->throwValidationExceptionsOnDispatch) {
                        throw new ValidationException($result);
                    }
                }
            }

            $messages[] = $event->toMessage();
        }

        $this->adapter->publishBatch($channel, $messages);
    }
}
