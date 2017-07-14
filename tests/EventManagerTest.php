<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\AttributeInjectorInterface;
use Superbalist\EventPubSub\AttributeInjectors\GenericAttributeInjector;
use Superbalist\EventPubSub\EventInterface;
use Superbalist\EventPubSub\EventManager;
use Superbalist\EventPubSub\Events\SimpleEvent;
use Superbalist\EventPubSub\EventValidatorInterface;
use Superbalist\EventPubSub\MessageTranslatorInterface;
use Superbalist\PubSub\PubSubAdapterInterface;

class EventManagerTest extends TestCase
{
    public function testGetAdapter()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $manager = new EventManager($adapter, $translator);
        $this->assertSame($adapter, $manager->getAdapter());
    }

    public function testGetTranslator()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $manager = new EventManager($adapter, $translator);
        $this->assertSame($translator, $manager->getTranslator());
    }

    public function testGetValidator()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $validator = Mockery::mock(EventValidatorInterface::class);
        $manager = new EventManager($adapter, $translator, $validator);
        $this->assertSame($validator, $manager->getValidator());
    }

    public function testGetAttributeInjectors()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $validator = Mockery::mock(EventValidatorInterface::class);
        $injector = Mockery::mock(AttributeInjectorInterface::class);
        $manager = new EventManager($adapter, $translator, $validator, [$injector]);
        $injectors = $manager->getAttributeInjectors();
        $this->assertEquals(1, count($injectors));
        $this->assertSame($injector, $injectors[0]);
    }

    public function testAddAttributeInjector()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $validator = Mockery::mock(EventValidatorInterface::class);
        $injector1 = Mockery::mock(AttributeInjectorInterface::class);
        $manager = new EventManager($adapter, $translator, $validator, [$injector1]);
        $injectors = $manager->getAttributeInjectors();
        $this->assertEquals(1, count($injectors));
        $this->assertSame($injector1, $injectors[0]);

        $injector2 = Mockery::mock(AttributeInjectorInterface::class);
        $manager->addAttributeInjector($injector2);
        $injectors = $manager->getAttributeInjectors();
        $this->assertEquals(2, count($injectors));
        $this->assertSame($injector1, $injectors[0]);
        $this->assertSame($injector2, $injectors[1]);
    }

    public function testGetSetTranslateFailHandler()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $handler1 = Mockery::mock(\stdClass::class);
        $callable1 = [$handler1, 'handle'];
        $manager = new EventManager($adapter, $translator, null, [], $callable1);
        $this->assertSame($callable1, $manager->getTranslateFailHandler());

        $handler2 = Mockery::mock(\stdClass::class);
        $callable2 = [$handler2, 'handle'];
        $manager->setTranslateFailHandler([$handler2, 'handle']);
        $this->assertSame($callable2, $manager->getTranslateFailHandler());
    }

    public function testGetSetListenExprFailHandler()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $handler1 = Mockery::mock(\stdClass::class);
        $callable1 = [$handler1, 'handle'];
        $manager = new EventManager($adapter, $translator, null, [], null, $callable1);
        $this->assertSame($callable1, $manager->getListenExprFailHandler());

        $handler2 = Mockery::mock(\stdClass::class);
        $callable2 = [$handler2, 'handle'];
        $manager->setListenExprFailHandler([$handler2, 'handle']);
        $this->assertSame($callable2, $manager->getListenExprFailHandler());
    }

    public function testGetSetValidationFailHandler()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $handler1 = Mockery::mock(\stdClass::class);
        $callable1 = [$handler1, 'handle'];
        $manager = new EventManager($adapter, $translator, null, [], null, null, $callable1);
        $this->assertSame($callable1, $manager->getValidationFailHandler());

        $handler2 = Mockery::mock(\stdClass::class);
        $callable2 = [$handler2, 'handle'];
        $manager->setValidationFailHandler([$handler2, 'handle']);
        $this->assertSame($callable2, $manager->getValidationFailHandler());
    }

    public function testListen()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter->shouldReceive('subscribe')
            ->withArgs([
                'user',
                Mockery::type('callable'),
            ]);

        $translator = Mockery::mock(MessageTranslatorInterface::class);

        $manager = new EventManager($adapter, $translator);

        $manager->listen('user', 'user/created', function () {
        });
    }

    public function testHandleSubscribeCallback()
    {
        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('matches')
            ->with('user/created')
            ->andReturn(true);

        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn($event);

        $validator = Mockery::mock(EventValidatorInterface::class);
        $validator->shouldReceive('validates')
            ->with($event)
            ->andReturn(true);

        $manager = new EventManager($adapter, $translator, $validator);

        $handler = Mockery::mock(\stdClass::class);
        $handler->shouldReceive('handle')
            ->with($event);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testHandleSubscribeCallbackWhenEventDoesNotTranslate()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn(null);

        $manager = new EventManager($adapter, $translator);

        $handler = Mockery::mock(\stdClass::class);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testHandleSubscribeCallbackWhenEventDoesNotTranslateAndHandlerIsCalled()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn(null);

        $manager = new EventManager($adapter, $translator);

        $translateFailHandler = Mockery::mock(\stdClass::class);
        $translateFailHandler->shouldReceive('handle')
            ->with('message payload');
        $manager->setTranslateFailHandler([$translateFailHandler, 'handle']);

        $handler = Mockery::mock(\stdClass::class);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testHandleSubscribeCallbackWhenEventDoesNotMatchListenExpression()
    {
        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('matches')
            ->with('user/created')
            ->andReturn(false);

        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn($event);

        $manager = new EventManager($adapter, $translator);

        $handler = Mockery::mock(\stdClass::class);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testHandleSubscribeCallbackWhenEventDoesNotMatchListenExpressionAndHandlerIsCalled()
    {
        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('matches')
            ->with('user/created')
            ->andReturn(false);

        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn($event);

        $manager = new EventManager($adapter, $translator);

        $listenExprFailHandler = Mockery::mock(\stdClass::class);
        $listenExprFailHandler->shouldReceive('handle')
            ->withArgs([
                $event,
                'user/created',
            ]);
        $manager->setListenExprFailHandler([$listenExprFailHandler, 'handle']);

        $handler = Mockery::mock(\stdClass::class);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testHandleSubscribeCallbackWithoutValidator()
    {
        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('matches')
            ->with('user/created')
            ->andReturn(true);

        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn($event);

        $manager = new EventManager($adapter, $translator);

        $handler = Mockery::mock(\stdClass::class);
        $handler->shouldReceive('handle')
            ->with($event);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testHandleSubscribeCallbackWhenValidationFails()
    {
        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('matches')
            ->with('user/created')
            ->andReturn(true);

        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn($event);

        $validator = Mockery::mock(EventValidatorInterface::class);
        $validator->shouldReceive('validates')
            ->with($event)
            ->andReturn(false);

        $manager = new EventManager($adapter, $translator, $validator);

        $handler = Mockery::mock(\stdClass::class);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testHandleSubscribeCallbackWhenValidationFailsAndHandlerIsCalled()
    {
        $event = Mockery::mock(EventInterface::class);
        $event->shouldReceive('matches')
            ->with('user/created')
            ->andReturn(true);

        $adapter = Mockery::mock(PubSubAdapterInterface::class);

        $translator = Mockery::mock(MessageTranslatorInterface::class);
        $translator->shouldReceive('translate')
            ->with('message payload')
            ->andReturn($event);

        $validator = Mockery::mock(EventValidatorInterface::class);
        $validator->shouldReceive('validates')
            ->with($event)
            ->andReturn(false);

        $manager = new EventManager($adapter, $translator, $validator);

        $validationFailHandler = Mockery::mock(\stdClass::class);
        $validationFailHandler->shouldReceive('handle')
            ->withArgs([
                $event,
                $validator,
            ]);
        $manager->setValidationFailHandler([$validationFailHandler, 'handle']);

        $handler = Mockery::mock(\stdClass::class);

        $manager->handleSubscribeCallback('message payload', 'user/created', [$handler, 'handle']);
    }

    public function testDispatch()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter->shouldReceive('publish')
            ->withArgs([
                'channel',
                [
                    'event' => 'user.created',
                    'user' => [
                        'id' => 1234,
                    ],
                ],
            ]);

        $translator = Mockery::mock(MessageTranslatorInterface::class);

        $manager = new EventManager($adapter, $translator);

        $event = new SimpleEvent('user.created', ['user' => ['id' => 1234]]);
        $manager->dispatch('channel', $event);
    }

    public function testDispatchWithInjections()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter->shouldReceive('publish')
            ->withArgs([
                'channel',
                [
                    'event' => 'user.created',
                    'user' => [
                        'id' => 1234,
                    ],
                    'service' => 'user-api',
                    'cluster' => 'api',
                ],
            ]);

        $translator = Mockery::mock(MessageTranslatorInterface::class);

        $injectors = [
            new GenericAttributeInjector('service', 'user-api'),
            function () {
                return [
                    'key' => 'cluster',
                    'value' => 'api',
                ];
            },
        ];

        $manager = new EventManager($adapter, $translator, null, $injectors);

        $event = new SimpleEvent('user.created', ['user' => ['id' => 1234]]);
        $manager->dispatch('channel', $event);

        // original event should not have injections as attributes
        $this->assertSame(['user' => ['id' => 1234]], $event->getAttributes());
    }

    public function testDispatchWithInjectionsDoNotOverrideExistingAttributes()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter->shouldReceive('publish')
            ->withArgs([
                'channel',
                [
                    'event' => 'user.created',
                    'user' => [
                        'id' => 1234,
                    ],
                    'date' => 'my_date',
                ],
            ]);

        $translator = Mockery::mock(MessageTranslatorInterface::class);

        $injectors = [
            new GenericAttributeInjector('date', 'injected_date'),
        ];

        $manager = new EventManager($adapter, $translator, null, $injectors);

        $event = new SimpleEvent('user.created', ['user' => ['id' => 1234], 'date' => 'my_date']);
        $manager->dispatch('channel', $event);
    }

    public function testDispatchBatch()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter->shouldReceive('publishBatch')
            ->withArgs([
                'channel',
                [
                    [
                        'event' => 'user.created',
                        'user' => [
                            'id' => 1234,
                        ],
                    ],
                    [
                        'event' => 'user.created',
                        'user' => [
                            'id' => 7812,
                        ],
                    ],
                ],
            ]);

        $translator = Mockery::mock(MessageTranslatorInterface::class);

        $manager = new EventManager($adapter, $translator);

        $events = [
            new SimpleEvent('user.created', ['user' => ['id' => 1234]]),
            new SimpleEvent('user.created', ['user' => ['id' => 7812]]),
        ];
        $manager->dispatchBatch('channel', $events);
    }

    public function testDispatchBatchWithInjections()
    {
        $adapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter->shouldReceive('publishBatch')
            ->withArgs([
                'channel',
                [
                    [
                        'event' => 'user.created',
                        'user' => [
                            'id' => 1234,
                        ],
                        'service' => 'user-api',
                        'cluster' => 'api',
                    ],
                    [
                        'event' => 'user.created',
                        'user' => [
                            'id' => 7812,
                        ],
                        'service' => 'user-api',
                        'cluster' => 'api',
                    ],
                ],
            ]);

        $translator = Mockery::mock(MessageTranslatorInterface::class);

        $injectors = [
            new GenericAttributeInjector('service', 'user-api'),
            function () {
                return [
                    'key' => 'cluster',
                    'value' => 'api',
                ];
            },
        ];

        $manager = new EventManager($adapter, $translator, null, $injectors);

        $events = [
            new SimpleEvent('user.created', ['user' => ['id' => 1234]]),
            new SimpleEvent('user.created', ['user' => ['id' => 7812]]),
        ];
        $manager->dispatchBatch('channel', $events);
    }
}
