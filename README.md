# php-event-pubsub

An event protocol and implementation over pub/sub

[![Author](http://img.shields.io/badge/author-@superbalist-blue.svg?style=flat-square)](https://twitter.com/superbalist)
[![Build Status](https://img.shields.io/travis/Superbalist/php-event-pubsub/master.svg?style=flat-square)](https://travis-ci.org/Superbalist/php-event-pubsub)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/superbalist/php-event-pubsub.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-event-pubsub)
[![Total Downloads](https://img.shields.io/packagist/dt/superbalist/php-event-pubsub.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-event-pubsub)

This library builds on top of the [php-pubsub](https://github.com/Superbalist/php-pubsub) package and adds support for
listening to and dispatching events over pub/sub channels.


## Installation

```bash
composer require superbalist/php-event-pubsub
```

## Usage

### Simple Events

A `SimpleEvent` is an event which takes a name and optional attributes.

```php
// create a new event manager
$adapter = new \Superbalist\PubSub\Adapters\LocalPubSubAdapter();
$translator = new \Superbalist\EventPubSub\Translators\SimpleEventMessageTranslator();
$manager = new \Superbalist\EventPubSub\EventManager($adapter, $translator);

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

// listen for an event
$manager->listen('events', 'user.created', function (\Superbalist\EventPubSub\EventInterface $event) {
    var_dump($event->getName());
    var_dump($event->getAttribute('user'));
});

// listen for all events on the channel
$manager->listen('events', '*', function (\Superbalist\EventPubSub\EventInterface $event) {
    var_dump($event->getName());
});
```

### Topic Events

A `TopicEvent` is an event which takes a topic, name, version and optional attributes.

```php
// create a new event manager
$adapter = new \Superbalist\PubSub\Adapters\LocalPubSubAdapter();
$translator = new \Superbalist\EventPubSub\Translators\TopicEventMessageTranslator();
$manager = new \Superbalist\EventPubSub\EventManager($adapter, $translator);

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

// listen for an event on a topic
$manager->listen('events', 'user/created', function (\Superbalist\EventPubSub\EventInterface $event) {
    // ...
});

// listen for an event on a topic matching the given version
$manager->listen('events', 'user/created/1.0', function (\Superbalist\EventPubSub\EventInterface $event) {
    // ...
});

// listen for all events on a topic
$manager->listen('events', 'user/*', function (\Superbalist\EventPubSub\EventInterface $event) {
    // ...
});

// listen for all events on the channel
$manager->listen('events', '*', function (\Superbalist\EventPubSub\EventInterface $event) {
    // ...
});
```

### Schema Events

A `SchemaEvent` is an extension of the `TopicEvent` and takes a schema and optional attributes.  The topic, name and
version are derived from the schema.

The schema must be in the format `(protocol)://(......)?/events/(topic)/(channel)/(version).json`

```php
// create a new event manager
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

// publish an event
$event = new \Superbalist\EventPubSub\Events\SchemaEvent(
    'http://schemas.my-website.org/events/user/created/1.0.json',
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

// the listen expressions are the same as those used for TopicEvents.
```

### Custom Events

You can easily use a custom event structure by writing a class which implements the `EventInterface` interface.
You will then need to write a custom translator to translate incoming messages to your own event object.

Your event must implement the following methods.

```php
/**
 * Return the event name.
 *
 * @return string
 */
public function getName();

/**
 * Return all event attributes.
 *
 * @return array
 */
public function getAttributes();

/**
 * Return an event attribute.
 *
 * @param string $name
 * @return mixed
 */
public function getAttribute($name);

/**
 * Check whether or not an event has an attribute.
 *
 * @param string $name
 * @return bool
 */
public function hasAttribute($name);

/**
 * Check whether or not the event matches the given expression.
 *
 * @param string $expr
 * @return bool
 */
public function matches($expr);

/**
 * Return the event in a message format ready for publishing.
 *
 * @return mixed
 */
public function toMessage();
```

## Translators

A translator is used to translate an incoming message into an event.

The package comes bundled with a `SimpleEventMessageTranslator`, `TopicEventMessageTranslator` and a
`SchemaEventMessageTranslator`.

### Custom Translators

You can easily write your own translator by implementing the `MessageTranslatorInterface` interface.

Your translator must implement the following methods.

```php
/**
 * @param mixed $message
 * @return null|EventInterface
 */
public function translate($message);
```

## Validators

A validator is an optional component used to validate an incoming event.

### JSONSchemaEventValidator

This package comes bundled with a `JSONSchemaEventValidator` which works with `SchemaEvent` type events.

This validator validates events against a [JSON Schema Spec](http://json-schema.org/) using the 
[JSON Guard](http://json-guard.thephpleague.com/dereferencing/overview/) PHP package.

Please see the "Schema Events" section above and the JSON Guard documentation for usage examples.

### Custom Validators

You can write your own validator by implementing the `EventValidatorInterface` interface.

Your validator must implement the following methods.

```php
/**
 * @param EventInterface $event
 * @return bool
 */
public function validates(EventInterface $event);
```