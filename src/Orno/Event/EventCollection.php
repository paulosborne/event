<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license MIT
 */
namespace Orno\Event;

use Orno\Di\ContainerAwareTrait;
use Orno\Http\Request;

/**
 * Event Collection
 *
 * Handles registration of events and returns event callbacks to be invoked
 */
class EventCollection
{
    /**
     * Container access
     */
    use ContainerAwareTrait;

    /**
     * Array of event listeners
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Build an event listener
     *
     * @throws \InvalidArgumentException
     * @param  string            $name
     * @param  string|\Closure   $event
     * @param  integer           $priority
     * @return \Orno\Event\Event
     */
    public function listen($name, $event, $priority = 0)
    {
        // is the event an invokable method?
        if (is_string($event)) {
            list($object, $method) = explode('::', $event);

            $alias = $object;

            if (! $this->getContainer()->registered($alias)) {
                $this->getContainer()->register($alias, $object, true);
            }
        }

        // is the event a closure?
        if ($event instanceof \Closure) {
            $object = $event;
            $method = null;
            $alias  = 'event.' . microtime();

            $this->getContainer()->register($alias, $object);
        }

        // bail out if the event is not valid
        if (! isset($alias)) {
            throw new \InvalidArgumentException(
                sprintf('%s expects parameter 2 to be of type string or Closure', __METHOD__)
            );
        }

        // build and store the event
        $event = new Event($name, $alias, $method);
        $this->listeners[$event][$priority][] = $event;

        return $event;
    }

    /**
     * Returns an array of event listeners for a given event name or all
     * listeners if no event name is provided
     *
     * @param  string $name
     * @return array
     */
    public function getListeners($name = null)
    {
        if (is_null($name)) {
            return $this->listeners;
        }

        $events = [];

        if (array_key_exists($name, $this->listeners)) {
            $events = $this->listeners[$name];
            ksort($events);
        }

        return $events;
    }

    /**
     * Reflects on an object and invokes any method that matches the event name.
     * This is useful when triggering events contained within controllers.
     *
     * @param  string $name
     * @param  object $object
     * @return void
     */
    protected function triggerObjectEvent($name, $object)
    {
        if (! is_object($object)) {
            throw \InvalidArgumentException(
                sprintf('%s expects parameter 2 to be of type Object', __METHOD__)
            );
        }

        if ((new \ReflectionClass($object))->hasMethod($name)) {
            $method = new \ReflectionMethod($object, $name);
            $method->setAccessible(true);
            $method->invokeArgs($object, $args);
        }
    }

    /**
     * Trigger all callbacks listening for an event name, then proxies to
     * triggerObjectEvent()
     *
     * @param  string $name
     * @param  object $object
     * @param  array  $rules
     * @param  array  $args
     * @return string
     */
    public function trigger($name, $object = null, $rules = [], $args = [])
    {
        ob_start();

        // loop through listeners
        foreach ($this->getListeners($name) as $event) {
            $matched = [];

            // check all rules match
            foreach ($rules as $rule => $value) {
                $matched[] = $event->isRuleMatch($rule, $value);
            }

            // if any rule matches failed continue to next listener
            if (in_array(false, $matched)) {
                continue;
            }

            $event($args);
        }

        // do we have a class/object to check for callbacks?
        if (! is_null($object)) {
            $this->triggerObjectEvent($name, $object);
        }

        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
