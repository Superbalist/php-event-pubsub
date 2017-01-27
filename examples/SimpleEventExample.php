<?php

include __DIR__ . '/../vendor/autoload.php';

$adapter = new \Superbalist\PubSub\Adapters\LocalPubSubAdapter();
$translator = new \Superbalist\EventPubSub\Translators\SimpleEventMessageTranslator();
$manager = new \Superbalist\EventPubSub\EventManager($adapter, $translator);

// listen for "*" event
$manager->listen('events', '*', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener '*' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// listen for "user.created" event
$manager->listen('events', 'user.created', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener 'user.created' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// publish an event
$event = new \Superbalist\EventPubSub\Events\SimpleEvent(
    'user.created',
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
