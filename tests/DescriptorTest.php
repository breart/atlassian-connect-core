<?php

namespace AtlassianConnectCore\Tests;

class DescriptorTest extends TestCase
{
    /**
     * @var \AtlassianConnectCore\Descriptor
     */
    protected $descriptor;

    /**
     * @inheritdoc
     */
    public function setUp()
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

        static::assertEquals($modules, array_get($this->descriptor->contents(), 'modules'));
    }

    public function testWithoutModules()
    {
        $this->testWithModules();

        static::assertNotEmpty(array_get($this->descriptor->contents(), 'modules'));

        $this->descriptor->withoutModules();

        static::assertEquals([], array_get($this->descriptor->contents(), 'modules'));
    }

    public function testSetScopes()
    {
        $scopes = ['ACT_AS_USER', 'ADMIN'];

        $this->descriptor->setScopes($scopes);

        static::assertEquals($scopes, array_get($this->descriptor->contents(), 'scopes'));
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