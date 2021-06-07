<?php

namespace AtlassianConnectCore;

use Illuminate\Events\Dispatcher;

/**
 * Class Webhook
 *
 * @package AtlassianConnectCore
 */
class Webhook
{
    /**
     * The event dispatcher
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Plugin constructor.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Register a webhook listener
     *
     * @param string $name
     * @param \Closure|string $listener Closure or class name
     *
     * @return void
     */
    public function listen(string $name, $listener)
    {
        // Define a webhook in the descriptor
        \AtlassianConnectCore\Facades\Descriptor::webhook($name, $this->url($name));

        // Register event listener
        $this->dispatcher->listen($this->eventName($name), $listener);
    }

    /**
     * Fire a webhook listeners
     *
     * @param string $name
     * @param array $payload
     *
     * @return void
     */
    public function fire(string $name, array $payload)
    {
        $this->dispatcher->dispatch($this->eventName($name), $payload);
    }

    /**
     * Get all listeners of the event
     *
     * @param string $name
     *
     * @return array
     */
    public function getListeners(string $name): array
    {
        return $this->dispatcher->getListeners($this->eventName($name));
    }

    /**
     * Create a webhook URL
     *
     * @param string $name Webhook name
     * @param bool $absolute Whether need to return an absolute URL
     *
     * @return string
     */
    public function url(string $name, bool $absolute = false)
    {
        return route('webhook', ['name' => $name], $absolute);
    }

    /**
     * Add a prefix to the event name
     *
     * @param string $name Webhook event name
     *
     * @return string
     */
    private function eventName(string $name)
    {
        return 'webhook:' . $name;
    }
}
