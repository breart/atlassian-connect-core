<?php

namespace AtlassianConnectCore\Tests\Listeners;

class UpdateTenantState extends \AtlassianConnectCore\Tests\TestCase
{
    /**
     * @var \AtlassianConnectCore\Services\TenantService
     */
    protected $tenantService;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->tenantService = $this->app->make(\AtlassianConnectCore\Services\TenantService::class);
        $this->request = $this->createTenantRequest();
    }

    public function testHandle()
    {
        $tenant = $this->createTenant();

        $clientKey = $this->request->input('clientKey');

        $listener = new \AtlassianConnectCore\Listeners\UpdateTenantState($this->tenantService);

        $listener->handle(new \AtlassianConnectCore\Events\Enabled($this->request, $tenant));
        $enabled = $this->tenantService->findByClientKey($clientKey);

        $listener->handle(new \AtlassianConnectCore\Events\Disabled($this->request));
        $disabled = $this->tenantService->findByClientKey($clientKey);

        $listener->handle(new \AtlassianConnectCore\Events\Installed($this->request));
        $installed = $this->tenantService->findByClientKey($clientKey);

        $listener->handle(new \AtlassianConnectCore\Events\Uninstalled($this->request, $tenant));
        $uninstalled = $this->tenantService->findByClientKey($clientKey);

        static::assertEquals('enabled', $enabled->event_type);
        static::assertEquals('disabled', $disabled->event_type);
        static::assertEquals('installed', $installed->event_type);
        static::assertEquals('uninstalled', $uninstalled->event_type);
    }
}