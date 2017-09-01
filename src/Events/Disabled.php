<?php

namespace AtlassianConnectCore\Events;

/**
 * Class Disabled
 *
 * @package AtlassianConnectCore\Events
 */
class Disabled
{
    /**
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * Enabled constructor.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }
}