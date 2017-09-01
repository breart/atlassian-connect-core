<?php

namespace AtlassianConnectCore\Tests;

use AtlassianConnectCore\Services\TenantService;

class TenantServiceTest extends TestCase
{
    /**
     * @var TenantService
     */
    protected $tenantService;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->tenantService = $this->app->make(TenantService::class);
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testAll()
    {
        $tenant = $this->createTenant();

        $attributes = $tenant->getAttributes();

        $itemAttributes = $this->tenantService->all()
            ->where('id', $tenant->id)
            ->first()
            ->toArray();

        $itemAttributes = collect($itemAttributes)
            ->except(['remember_token', 'deleted_at'])
            ->all();

        static::assertEquals($attributes, $itemAttributes);
    }

    public function testUpdateState()
    {
        $tenant = $this->createTenant();
        $clientKey = $tenant->client_key;

        $this->tenantService->updateState($clientKey, 'enabled');
        $found = $this->tenantService->findByClientKey($clientKey);

        static::assertEquals('enabled', $found->event_type);
    }

    public function testFindRealsAndFindDefaultDummy()
    {
        $tenant = $this->createTenant(['is_dummy' => true]);
        $found = $this->tenantService->dummy();

        static::assertArraySubset($tenant->getAttributes(), $found->getAttributes());
    }

    public function testMakeDummy()
    {
        $tenant = $this->createTenant(['is_dummy' => false]);

        $this->tenantService->makeDummy($tenant->id);

        $found = $this->tenantService->findByClientKey($tenant->client_key);

        static::assertTrue($found->isDummy());
    }

    public function testDelete()
    {
        $tenant = $this->createTenant();

        $this->tenantService->delete($tenant->id);

        $found = $this->tenantService->findByClientKey($tenant->client_key, false);

        static::assertEmpty($found);
    }
}