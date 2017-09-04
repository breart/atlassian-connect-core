<?php

namespace AtlassianConnectCore\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * Class SampleController
 *
 * @package AtlassianConnectCore\Http\Controllers
 */
class SampleController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('plugin::hello');
    }
}