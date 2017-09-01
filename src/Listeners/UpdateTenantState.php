<?php

namespace AtlassianConnectCore\Listeners;

use AtlassianConnectCore\Events\Disabled;
use AtlassianConnectCore\Events\Enabled;
use AtlassianConnectCore\Events\Installed;
use AtlassianConnectCore\Events\Uninstalled;
use AtlassianConnectCore\Services\TenantService;

/**
 * Class UpdateTenantState
 *
 * @package App\Listeners
 */
class UpdateTenantState
{
    /**
     * @var TenantService
     */
    protected $tenantService;

    /**
     * CreateOrUpdateTenant constructor.
     *
     * @param TenantService $tenantService
     */
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle the event.
     *
     * @param Installed|Uninstalled|Enabled|Disabled $event
     */
    public function handle($event)
    {
        $this->tenantService->updateState(
            $event->request->input('clientKey'),
            $event->request->input('eventType')
        );
    }
}
