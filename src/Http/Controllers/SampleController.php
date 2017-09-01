<?php

namespace AtlassianConnectCore\Http\Controllers;

use App\Http\Controllers\Controller;

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