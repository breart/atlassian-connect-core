<?php

namespace AtlassianConnectCore\Tests;

use Illuminate\Support\Arr;

class DescriptorTest extends TestCase
{
    /**
     * @var \AtlassianConnectCore\Descriptor
     */
    protected $descriptor;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->descriptor = $this->createDescriptor();
    }

    public function testMerge()
    {
        $this->descriptor->merge([
            'version' => '1.0.1',
            'vendor' => [
                'email' => 'brezzhnev@gmail.com'
            ]
        ]);

        static::assertEquals([
            'name' => 'Sample add-on',
            'version' => '1.0.1',
            'vendor' => [
                'email' => 'brezzhnev@gmail.com'
            ]
        ], $this->descriptor->contents());
    }

    public function testModify()
    {
        $this->descriptor->modify(function (array &$contents) {
            $contents['name'] = 'Modified add-on';
        });

        static::assertArraySubset(['name' => 'Modified add-on'], $this->descriptor->contents());

        $this->flush();

        $this->descriptor->modify(function (array $contents) {
            $contents['url'] = 'http://modified.com';
            return $contents;
        });

        static::assertArraySubset(
            ['name' => 'Sample add-on', 'url' => 'http://modified.com'],
            $this->descriptor->contents()
        );
    }

    public function testOverwrite()
    {
        $this->descriptor->overwrite(['test' => 'value']);

        static::assertEquals(['test' => 'value'], $this->descriptor->contents());
    }

    public function testSet()
    {
        $this->descriptor->set('name', 'Updated name');
        $this->descriptor->set('vendor.url', 'http://example.com');

        static::assertArraySubset(
            ['name' => 'Updated name', 'vendor' => ['name' => 'test', 'url' => 'http://example.com']],
            $this->descriptor->contents()
        );
    }

    public function testGet()
    {
        $version = $this->descriptor->get('version');
        $vendorName = $this->descriptor->get('vendor.name');
        $unknown = $this->descriptor->get('vendor.email');

        static::assertEquals($version, '1.0.0');
        static::assertEquals($vendorName, 'test');
        static::assertEquals($unknown, null);
    }

    public function testBase()
    {
        $this->descriptor->base();

        static::assertEquals(
            ['name', 'description', 'key', 'baseUrl', 'vendor', 'version', 'authentication', 'lifecycle'],
            array_keys($this->descriptor->contents())
        );
    }

    public function testFluent()
    {
        static::assertInstanceOf(\AtlassianConnectCore\Descriptor::class, $this->descriptor->fluent());
    }

    public function testWithModules()
    {
        $modules = [
            'webhooks' => [[
                'event' => 'jira:issue_created',
                'url' => '/'
            ]]
        ];

        $this->descriptor->withModules($modules);

        static::assertEquals($modules, Arr::get($this->descriptor->contents(), 'modules'));
    }

    public function testWithoutModules()
    {
        $this->testWithModules();

        static::assertNotEmpty(Arr::get($this->descriptor->contents(), 'modules'));

        $this->descriptor->withoutModules();

        static::assertEquals([], Arr::get($this->descriptor->contents(), 'modules'));
    }

    public function testSetScopes()
    {
        $scopes = ['ACT_AS_USER', 'ADMIN'];

        $this->descriptor->setScopes($scopes);

        static::assertEquals($scopes, Arr::get($this->descriptor->contents(), 'scopes'));
    }

    /**
     * @covers Descriptor::webhook
     * @covers Descriptor::webhooks
     */
    public function testWebhook()
    {
        $this->descriptor->webhook('jira:issue_created', '/test');
        $this->descriptor->webhook('jira:issue_updated', '/test-update');

        static::assertEquals([
            ['event' => 'jira:issue_created', 'url' => '/test'],
            ['event' => 'jira:issue_updated', 'url' => '/test-update']
        ], Arr::get($this->descriptor->contents(), 'modules.webhooks'));

        $this->descriptor->webhooks([
            'jira:issue_created' => '/test-create',
            'jira:issue_deleted' => '/test-delete'
        ]);

        static::assertEquals([
            ['event' => 'jira:issue_created', 'url' => '/test-create'],
            ['event' => 'jira:issue_updated', 'url' => '/test-update'],
            ['event' => 'jira:issue_deleted', 'url' => '/test-delete']
        ], Arr::get($this->descriptor->contents(), 'modules.webhooks'));
    }

    /**
     * Create descriptor instance
     *
     * @return \AtlassianConnectCore\Descriptor
     */
    protected function createDescriptor()
    {
        return new \AtlassianConnectCore\Descriptor([
            'name' => 'Sample add-on',
            'version' => '1.0.0',
            'vendor' => [
                'name' => 'test',
                'url' => 'http://localhost'
            ]
        ]);
    }

    /**
     * Flush descriptor instance
     */
    protected function flush()
    {
        $this->descriptor = $this->createDescriptor();
    }
}