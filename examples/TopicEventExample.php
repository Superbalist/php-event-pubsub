<?php

include __DIR__ . '/../vendor/autoload.php';

$adapter = new \Superbalist\PubSub\Adapters\LocalPubSubAdapter();
$translator = new \Superbalist\EventPubSub\Translators\TopicEventMessageTranslator();
$manager = new \Superbalist\EventPubSub\EventManager($adapter, $translator);

// listen for "*" event
$manager->listen('events', '*', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener '*' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// listen for "user/created" event
$manager->listen('events', 'user/created', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener 'user/created' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// listen for "user/*" event
$manager->listen('events', 'user/*', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener 'user/*' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// listen for "user/created/1.0" event
$manager->listen('events', 'user/created/1.0', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener 'user/created/1.0' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// listen for "order/created" event
$manager->listen('events', 'order/created', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener 'order/created' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// publish an event
$event = new \Superbalist\EventPubSub\Events\TopicEvent(
    'user',
    'created',
    '1.0',
    [
        'user' => [
            'id' => 1456,
            'first_name' => 'Joe',
            'last_name' => 'Soap',
            'email' => 'joe.soap@example.org',
        ],
    ]
);
$manager->dispatch('events', $event);

// publish an event
$event = new \Superbalist\EventPubSub\Events\TopicEvent(
    'order',
    'created',
    '2.1',
    [
        'order' => [
            'id' => 1456,
        ],
    ]
);
$manager->dispatch('events', $event);
