# Orno\Event

A simple and intuitive Event Manager that allows you to trigger events based on regex rules, providing fine grained control for when your event callbacks are invoked.

## Simple Usage

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
