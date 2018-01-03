<?php

namespace AtlassianConnectCore\Listeners;

/**
 * Class PluginEventSubscriber
 *
 * @package AtlassianConnnectCore\Listeners
 */
class PluginEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(\AtlassianConnectCore\Events\Installed::class, CreateOrUpdateTenant::class);
        $events->listen(\AtlassianConnectCore\Events\Uninstalled::class, DeleteTenant::class);

        $events->listen([
            \AtlassianConnectCore\Events\Installed::class,
            \AtlassianConnectCore\Events\Uninstalled::class,
            \AtlassianConnectCore\Events\Enabled::class,
            \AtlassianConnectCore\Events\Disabled::class,
        ], UpdateTenantState::class);
    }

}