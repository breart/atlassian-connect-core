<?php

namespace AtlassianConnectCore\Tests\Listeners;

class CreateOrUpdateTenantTest extends \AtlassianConnectCore\Tests\TestCase
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
    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantService = $this->app->make(\AtlassianConnectCore\Services\TenantService::class);
        $this->request = $this->createTenantRequest();
    }

    public function testHandle()
    {
        $listener = new \AtlassianConnectCore\Listeners\CreateOrUpdateTenant($this->tenantService);
        $listener->handle(new \AtlassianConnectCore\Events\Installed($this->request));

        static::assertInstanceOf(
            \AtlassianConnectCore\Models\Tenant::class,
            $this->tenantService->findByClientKey($this->request->input('clientKey'))
        );
    }
}