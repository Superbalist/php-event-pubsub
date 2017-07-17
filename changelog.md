# Changelog

## 3.0.1 - 2017-07-17

* Add support for translate, listen expr & validation failure callbacks

## 3.0.0 - 2017-05-16

* Bump up to superbalist/php-pubsub ^2.0
* Add new dispatchBatch method to EventManager for dispatching multiple events at once

## 2.0.0 - 2017-02-01

* Add `setAttribute` method to `EventInterface`
* Add support for "attribute injectors" with `AttributeInjectorInterface`
* Bundle `DateAttributeInjector`, `GenericAttributeInjector`, `HostnameAttributeInjector`, `Uuid4AttributeInjector`
* Add new `addAttributeInjector` and `getAttributeInjectors` methods to `EventManager`

## 1.0.0 - 2017-01-27

* Initial release