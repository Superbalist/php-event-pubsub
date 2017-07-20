# Changelog

## 4.0.2 - 2017-07-20

* Fix events not being validated correctly when attribute injectors are used
* Events are no longer validated when received, only upon dispatch

## 4.0.1 - 2017-07-18

* Fix events dispatching when validation fails & exceptions are suppressed

## 4.0.0 - 2017-07-17

* EventValidatorInterface->validates() renamed to ->validate()
* EventValidatorInterface->validate() now returns a ValidationResult instance instead of bool
* The validation fail handler callback now receives a ValidationResult instead of the event and a validator
* Events are now validated on dispatch, and will throw a ValidationException if throwValidationExceptionsOnDispatch is true (defaults to true)
* Added new throwValidationExceptionsOnDispatch(bool) method to EventManager to suppress validation exceptions on dispatch

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