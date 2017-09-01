<?php

namespace AtlassianConnectCore\Tests\Listeners;

class DeleteTenantTest extends \AtlassianConnectCore\Tests\TestCase
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

        $listener = new \AtlassianConnectCore\Listeners\DeleteTenant($this->tenantService);
        $listener->handle(new \AtlassianConnectCore\Events\Uninstalled($this->request, $tenant));

        $found = $this->tenantService->findByClientKey($this->request->input('clientKey'));

        static::assertInstanceOf(\AtlassianConnectCore\Models\Tenant::class, $found);
        static::assertNotEmpty($found->deleted_at);
    }
}