# Orno\Event

A simple and intuitive Event Manager that allows you to trigger events based on regex rules, providing fine grained control for when your event callbacks are invoked.

## Installation

Orno\Event is available on Packagist so the easiest way to install it into your project is via Composer. You han get more information about composer [here](http://getcomposer.org/doc/00-intro.md).

Simply add orno/event to your `composer.json` file like so:

    "require": {
        "orno/event": "dev-master"
    }

## Usage

### Simple Callbacks

```php
<?php

$event = new Orno\Event\EventCollection;

// create an event listener with callback
$event->listen('sayHello', function () {
    echo 'Phil!';
}, 1);

// create another event listener
$event->listen('sayHello', function () {
    echo 'Hello ';
}, 0);

// trigger the events
$event->trigger('sayHello');
```

Event listeners are called in priority order, the `listen()` method accepts an integer as it's third parameter, the lower the number, the earlier the callback will be invoked. So the above code will output the following based on the listener priorities.

```
Hello Phil!
```

### Grouping Callbacks

You may wish to group related callbacks within a class/object.

```php
<?php

class EventCallbacks
{
    public function sayHello()
    {
        echo 'Hello ';
    }

    public function sayPhil()
    {
        echo 'Phil!';
    }
}
```

The above methods can quite easily be attached to an event listener.

```php
<?php

$event = new Orno\Event\EventCollection;

// we can attach a Class::method callback to an event listener like so
$event->listen('someEvent', 'EventCallbacks::sayHello', 0);
$event->listen('someEvent', 'EventCallbacks::sayPhil', 1);

$event->trigger('someEvent');
```

### Object Callbacks

There may be a situation where you would like to trigger callbacks from within a class/object.

For example, in an MVC application you may wish to trigger a callback every time a controller is resolved. This can be done by creating a method that matches the event name.

```php
<?php

class SomeController
{
    public function onDispatch()
    {
        // define some logic to be run on the 'onDispatch' event
    }

    public function someAction()
    {
        // a standard controller action
    }
}
```

You may also wish to run a callback on the same event for every request.

```php
$event->listen('onDisptach', function () {
    // some logging functionality
});
```

Now, once we resolve the controller we can trigger this event before dispatching the controller action, by passing the controller object in to the `trigger()` method as it's second argument, the `onDispatch()` method will be invoked as it matches the event name.

```php
$controller = SomeController;

$event->trigger('onBootstrap', $controller);
```
