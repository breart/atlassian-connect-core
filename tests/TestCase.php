<?php

namespace AtlassianConnectCore\Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->migrate();

        \Illuminate\Support\Facades\DB::beginTransaction();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        \Mockery::close();

        \Illuminate\Support\Facades\DB::rollBack();

        parent::tearDown();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \AtlassianConnectCore\ServiceProvider::class
        ];
    }

    /**
     * Load package alias
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Descriptor' => \AtlassianConnectCore\Facades\Descriptor::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application    $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(__DIR__ . '/files');

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => ''
        ]);

        $app['config']->set('auth.providers.users.model', \AtlassianConnectCore\ServiceProvider::class);
        $app['config']->set('auth.guards.web.driver', 'jwt');
    }

    /**
     * Run migrations
     */
    protected function migrate()
    {
        if (app()->version() >= 5.4) {
            $migrator = app('migrator');

            if (!$migrator->repositoryExists()) {
                $this->artisan('migrate:install');
            }

            $migrator->run([__DIR__ . '/../database/migrations']);

            $this->artisan('migrate', ['--path' => __DIR__ . '/../database/migrations']);
        }
    }

    /**
     * Create a tenant instance
     *
     * @param array $attributes
     * @param bool $save Whether model should be saved
     *
     * @return \AtlassianConnectCore\Models\Tenant
     */
    protected function createTenant(array $attributes = [], $save = true)
    {
        $tenant = new \AtlassianConnectCore\Models\Tenant();
        $tenant->fill(array_merge($this->tenantData(), $attributes));

        if($save) {
            $tenant->save();
        }

        return $tenant;
    }

    /**
     * Create tenant request
     *
     * @param array $merge
     * @param array $requestClass
     *
     * @return \Illuminate\Http\Request
     */
    protected function createTenantRequest(array $merge = [], $requestClass = null)
    {
        $class = ($requestClass === null ? \Illuminate\Http\Request::class : $requestClass);

        return new $class(array_merge([
            'key'            => $this->tenantData('addon_key'),
            'clientKey'      => $this->tenantData('client_key'),
            'publicKey'      => $this->tenantData('public_key'),
            'sharedSecret'   => $this->tenantData('shared_secret'),
            'serverVersion'  => $this->tenantData('server_version'),
            'pluginsVersion' => $this->tenantData('plugin_version'),
            'baseUrl'        => $this->tenantData('base_url'),
            'productType'    => $this->tenantData('product_type'),
            'description'    => $this->tenantData('description'),
            'eventType'      => $this->tenantData('event_type')
        ], $merge));
    }

    /**
     * Retrieve fake data of the tenant
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    protected function tenantData($key = null, $default = null)
    {
        $data = [
            'addon_key' => 'test',
            'client_key' => 'c4fdbf9b-0a07-4654-9442-239406ae4e07',
            'public_key' => 'test',
            'shared_secret' => 'af7EKBf79AuaqBEthgiXIqEaEBsxYqndLFh/8VuSPeqE8flI6nJCCLRODOPwQpAXyasUm/f01/h7+diwqMdAYa',
            'server_version' => '100058',
            'plugin_version' => '1.3.175',
            'base_url' => 'https://test.atlassian.net',
            'product_type' => 'jira',
            'description' => 'Testing tenant',
            'event_type' => 'installed'
        ];

        return ($key === null ? $data : array_get($data, $key, $default));
    }
}