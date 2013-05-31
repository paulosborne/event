<?php
/**
 * The Orno Component Library
 *
 * @author  Phil Bennett @philipobenito
 * @license MIT
 */
namespace Orno\Event;

use Orno\Di\ContainerAwareTrait;

/**
 * Event
 *
 * Holds information relating to an event callback
 */
class Event
{
    /**
     * Container access
     */
    use ContainerAwareTrait;

    /**
     * The event name
     *
     * @var string
     */
    protected $name;

    /**
     * Alias of the object registered with the container
     *
     * @var string
     */
    protected $alias;

    /**
     * Method name to invoke as a callback
     *
     * @var string
     */
    protected $method;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $alias
     * @param string $method
     */
    public function __construct($name, $alias, $method = null)
    {
        $this->name   = $name;
        $this->alias  = $alias;
        $this->method = $method;
    }

    /**
     * Invoke the event object
     *
     * @param  array $args
     * @return void
     */
    public function __invoke(array $args)
    {
        $object = $this->getContainer()->resolve($alias);

        if (! is_null($this->method)) {
            call_user_func_array([$object, $this->method], $args);
        }
    }

    /**
     * Adds a named pattern that must be matched for the event to be invoked.
     * Helpful if you want to attach an event to a route or module.
     *
     * @param  string $key
     * @param  string $pattern
     * @return \Orno\Event\Event
     */
    public function withRule($rule, $pattern)
    {
        $this->rules[$rule] = $pattern;

        return $this;
    }

    /**
     * Proxy to withRule method for multiple entries
     *
     * @param  array $rules
     * @return \Orno\Event\Event
     */
    public function withRules(array $rules)
    {
        foreach ($rules as $rule => $pattern) {
            $this->withRule($rule, $pattern);
        }

        return $this;
    }

    /**
     * Checks for a pattern match on registered rules
     *
     * @param  string $rule
     * @param  string $value
     * @return boolean
     */
    public function isRuleMatch($rule, $value)
    {
        // if there is no rule set then it means the rule passes
        // as there are no restrictions
        if (! array_key_exists($rule, $this->rules)) {
            return true;
        }

        return (bool) preg_match('#^' . $this->rules[$rule] . '$#', $value);
    }
}
