<?php

namespace AtlassianConnectCore\Events;

/**
 * Class Uninstalled
 *
 * @package AtlassianConnectCore\Events
 */
class Uninstalled
{
    /**
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * @var object
     */
    public $tenant;

    /**
     * Uninstalled constructor.
     *
     * @param \Illuminate\Http\Request $request
     * @param object $tenant
     */
    public function __construct($request, $tenant)
    {
        $this->request = $request;
        $this->tenant = $tenant;
    }
}