<?php

namespace AtlassianConnectCore\Events;

/**
 * Class Installed
 *
 * @package AtlassianConnectCore\Events
 */
class Installed
{
    /**
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * Installed constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }
}