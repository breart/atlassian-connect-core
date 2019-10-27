<?php

namespace AtlassianConnectCore\Tests;

use Illuminate\Support\Facades\File;

class InstallCommandTest extends TestCase
{
    public function tearDown(): void
    {
        File::deleteDirectory(base_path(), true);
    }

    public function testDummyTenantCreatedAndResourcesPublished()
    {
        $this->artisan('plugin:install');

        static::assertFileExists(config_path('plugin.php'));
        static::assertFileExists(public_path('vendor/plugin/package.png'));
        static::assertFileExists(resource_path('views/vendor/plugin/layout.blade.php'));
        static::assertFileExists(resource_path('views/vendor/plugin/hello.blade.php'));
    }
}