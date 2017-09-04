<?php

namespace AtlassianConnectCore\Listeners;

use AtlassianConnectCore\Services\TenantService;

/**
 * Class UpdateTenantState
 *
 * @package AtlassianConnectCore\Listeners
 */
class DeleteTenant
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
     * @param \AtlassianConnectCore\Events\Uninstalled $event
     */
    public function handle($event)
    {
        $this->tenantService->delete($event->tenant->id);
    }
}
