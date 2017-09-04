<?php

namespace AtlassianConnectCore\Http\Controllers;

use Illuminate\Routing\Controller;
use AtlassianConnectCore\Facades\Descriptor;
use AtlassianConnectCore\Services\TenantService;

/**
 * Class TenantController
 *
 * @package AtlassianConnectCore\Http\Controllers
 */
class TenantController extends Controller
{
    /**
     * @var TenantService
     */
    protected $tenantService;

    /**
     * TenantController constructor.
     *
     * @param TenantService $tenantService
     */
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Descriptor contents
     */
    public function descriptor()
    {
        return Descriptor::contents();
    }

    /**
     * Add-on installed callback
     *
     * @param \AtlassianConnectCore\Http\Requests\InstalledRequest $request
     */
    public function installed(\AtlassianConnectCore\Http\Requests\InstalledRequest $request)
    {
        event(new \AtlassianConnectCore\Events\Installed($request));
    }

    /**
     * Add-on uninstalled callback
     *
     * @param \AtlassianConnectCore\Http\Requests\UninstalledRequest $request
     */
    public function uninstalled(\AtlassianConnectCore\Http\Requests\UninstalledRequest $request)
    {
        $tenant = $this->tenantService->findByClientKeyOrFail($request->get('clientKey'));

        event(new \AtlassianConnectCore\Events\Uninstalled($request, $tenant));
    }

    /**
     * Add-on enabled callback
     *
     * @param \AtlassianConnectCore\Http\Requests\EnabledRequest $request
     */
    public function enabled(\AtlassianConnectCore\Http\Requests\EnabledRequest $request)
    {
        event(new \AtlassianConnectCore\Events\Enabled($request));
    }

    /**
     * Add-on disabled callback
     *
     * @param \AtlassianConnectCore\Http\Requests\DisabledRequest $request
     */
    public function disabled(\AtlassianConnectCore\Http\Requests\DisabledRequest $request)
    {
        event(new \AtlassianConnectCore\Events\Disabled($request));
    }
}