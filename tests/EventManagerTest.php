<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\EventPubSub\AttributeInjectorInterface;
use Superbalist\EventPubSub\AttributeInjectors\GenericAttributeInjector;
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
}
