<?php

namespace AtlassianConnectCore\Listeners;

use AtlassianConnectCore\Services\TenantService;

/**
 * Class CreateOrUpdateTenant
 *
 * @package AtlassianConnectCore\Listeners
 */
class CreateOrUpdateTenant
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
     * @param \AtlassianConnectCore\Events\Installed $event
     */
    public function handle($event)
    {
        $tenant = $this->tenantService->createOrUpdate([
            'addon_key' => $event->request->input('key'),
            'client_key' => $event->request->input('clientKey'),
            'public_key' => $event->request->input('publicKey'),
            'oauth_client_token' => $event->request->input('oauthClientId'),
            'shared_secret' => $event->request->input('sharedSecret'),
            'server_version' => $event->request->input('serverVersion'),
            'plugin_version' => $event->request->input('pluginsVersion'),
            'base_url' => $event->request->input('baseUrl'),
            'product_type' => $event->request->input('productType'),
            'description' => $event->request->input('description'),
            'event_type' => $event->request->input('eventType')
        ]);

        // If tenant is trashed we need "un-delete" it because add-on installing implies enabling
        if($tenant->trashed()) {
            $tenant->restore();
        }
    }
}
