<?php

namespace AtlassianConnectCore\Console;

use AtlassianConnectCore\Services\TenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Class InstallCommand
 *
 * @package AtlassianConnectCore\Console
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the commands necessary to prepare plugin for development and use';

    /**
     * @var TenantService
     */
    protected $tenantService;

    /**
     * InstallCommand constructor.
     *
     * @param TenantService $tenantService
     */
    public function __construct(TenantService $tenantService)
    {
        parent::__construct();

        $this->tenantService = $tenantService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->createDummyTenant();
    }

    /**
     * Create the dummy tenant
     */
    protected function createDummyTenant()
    {
        if(!Schema::hasTable($tableName = config('plugin.tenant'))) {
            throw new \Exception('Table ' . $tableName . ' should be exist. Please, run migrations');
        }

        $this->tenantService->createOrUpdate([
            'addon_key' => config('plugin.key'),
            'client_key' => 'f8e11216-24ba-344e-91b8-845af3d945f0',
            'public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCK/XMT+GMfzH97nZD1Nj9riBgVZOO/vkJpBAltIEdPBigqHXuv7vG17QrTpzPZQ4ssrpD8RncnLGGevfEXbdNtx50+oUFMjQUde87uyOuBMa5LuhBu47++NEwQKXOC+uw+YJzLb564PDlZGp+OVcKuoDarC/zpw3LezQ2tEJB22QIDAQAB',
            'shared_secret' => 'vf7EKBf79AuaqBEthgiXIqEaEBsxYqndLFh/8VuSPeqE8flI6nJCCLRODOPwQpAXyasUm/f01/h7+diwqMdAYa',
            'server_version' => '100058',
            'plugin_version' => '1.3.175',
            'base_url' => 'https://test.atlassian.net',
            'product_type' => 'jira',
            'description' => 'Dummy tenant for local testing',
            'event_type' => 'installed',
            'is_dummy' => true
        ]);

        $this->info('Tenant for local development created successfully');

        $this->call('vendor:publish', ['--provider' => 'AtlassianConnectCore\ServiceProvider', '--force']);
    }
}