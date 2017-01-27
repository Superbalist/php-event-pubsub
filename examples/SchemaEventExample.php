<?php

include __DIR__ . '/../vendor/autoload.php';

$adapter = new \Superbalist\PubSub\Adapters\LocalPubSubAdapter();

$translator = new \Superbalist\EventPubSub\Translators\SchemaEventMessageTranslator();

$schemas = [
    'events/user/created/1.0.json' => json_encode([
        '$schema' => 'http://json-schema.org/draft-04/schema#',
        'title' => 'My Schema',
        'type' => 'object',
        'properties' => [
            'schema' => [
                'type' => 'string',
            ],
            'user' => [
                'type' => 'object',
            ],
        ],
        'required' => [
            'schema',
            'user',
        ],
    ]),
];
$loader = new \League\JsonGuard\Loaders\ArrayLoader($schemas);

$dereferencer = new \League\JsonGuard\Dereferencer();
$dereferencer->registerLoader($loader, 'array');

$validator = new \Superbalist\EventPubSub\Validators\JSONSchemaEventValidator($dereferencer);

$manager = new \Superbalist\EventPubSub\EventManager($adapter, $translator, $validator);

// listen for "user/created/1.0" event
$manager->listen('events', 'user/created/1.0', function (\Superbalist\EventPubSub\EventInterface $event) {
    echo "Listener '*' received new event on channel 'events':\n";
    echo "\n";
    var_dump($event);
    echo "\n\n";
});

// publish an event
$event = new \Superbalist\EventPubSub\Events\SchemaEvent(
    'array://events/user/created/1.0.json',
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