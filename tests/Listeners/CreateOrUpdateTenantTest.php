<?php

namespace AtlassianConnectCore\Tests\Listeners;

class CreateOrUpdateTest extends \AtlassianConnectCore\Tests\TestCase
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
        $this->request = new \Illuminate\Http\Request([
            'key' => 'test',
            'clientKey' => 'c4fdbf9b-0a07-4654-9442-239406ae4e07',
            'publicKey' => 'test',
            'sharedSecret' => 'af7EKBf79AuaqBEthgiXIqEaEBsxYqndLFh/8VuSPeqE8flI6nJCCLRODOPwQpAXyasUm/f01/h7+diwqMdAYa',
            'serverVersion' => '100058',
            'pluginsVersion' => '1.3.175',
            'baseUrl' => 'https://test.atlassian.net',
            'productType' => 'jira',
            'description' => 'Testing tenant',
            'eventType' => 'installed'
        ]);
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