<?php

namespace AtlassianConnectCore\Events;

/**
 * Class Enabled
 *
 * @package AtlassianConnectCore\Events
 */
class Enabled
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