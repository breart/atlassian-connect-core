<?php

namespace AtlassianConnectCore\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Webhook
 *
 * @package AtlassianConnectCore\Facades
 */
class Webhook extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'webhook';
    }
}