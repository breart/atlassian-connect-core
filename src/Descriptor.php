<?php

namespace AtlassianConnectCore;

use Illuminate\Support\Arr;

/**
 * Class Descriptor
 *
 * @package AtlassianConnectCore
 */
class Descriptor
{
    /**
     * Descriptor contents
     *
     * @var array
     */
    protected $contents = [];

    /**
     * Descriptor constructor.
     *
     * @param array $contents
     */
    public function __construct(array $contents = [])
    {
        $this->contents = (empty($contents) ? $this->defaultContents() : $contents);
    }

    /**
     * Returns the descriptor contents
     *
     * @return array
     */
    public function contents()
    {
        return $this->contents;
    }

    /**
     * Overwrite contents
     *
     * @param array $contents
     *
     * @return $this
     */
    public function overwrite(array $contents)
    {
        $this->contents = $contents;

        return $this;
    }

    /**
     * Set contents value using dot notation
     *
     * @param string $key
     * @param string|array $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        Arr::set($this->contents, $key, $value);

        return $this;
    }

    /**
     * Get value from contents using dot notation
     *
     * @param string $key
     * @param string|array $default
     *
     * @return array|string|null
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->contents, $key, $default);
    }

    /**
     * Merge descriptor contents
     *
     * @param array $contents
     *
     * @return $this
     */
    public function merge(array $contents)
    {
        $this->contents = array_merge($this->contents, $contents);

        return $this;
    }

    /**
     * Modify contents using callback function
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function modify(callable $callback)
    {
        $result = $callback($this->contents);

        $this->contents = (is_array($result) ? $result : $this->contents);

        return $this;
    }

    /**
     * The helper method to use fluent interface
     *
     * @return $this
     */
    public function fluent()
    {
        return $this;
    }

    /**
     * Set specific modules
     *
     * @param array $modules
     *
     * @return $this
     */
    public function withModules(array $modules)
    {
        $this->set('modules', $modules);

        return $this;
    }

    /**
     * Remove modules
     *
     * @return $this
     */
    public function withoutModules()
    {
        $this->set('modules', []);

        return $this;
    }

    /**
     * Set scopes
     *
     * @param array $scopes
     *
     * @return $this
     */
    public function setScopes(array $scopes)
    {
        $this->set('scopes', $scopes);

        return $this;
    }

    /**
     * Set base contents
     *
     * @return $this
     */
    public function base()
    {
        $this->contents = Arr::only($this->defaultContents(), [
            'name',
            'description',
            'key',
            'baseUrl',
            'vendor',
            'version',
            'authentication',
            'lifecycle'
        ]);

        return $this;
    }

    /**
     * Add or replace a webhook
     *
     * @param string $name
     * @param string $url
     */
    public function webhook(string $name, string $url)
    {
        $webhooks = $this->get('modules.webhooks', []);

        // Go through existing webhooks and if there is a webhook with the same name, just replace a url
        foreach ($webhooks as $key => $webhook) {
            if(Arr::get($webhook, 'event') === $name) {
                $this->set("modules.webhooks.$key.url", $url);
                return;
            }
        }

        $webhooks[] = [
            'event' => $name,
            'url' => $url
        ];

        $this->set('modules.webhooks', $webhooks);
    }

    /**
     * Define multiple webhooks
     *
     * [
     *   'jira:issue_created' => '/webhook-handler-url',
     *   ...
     * ]
     *
     * @param array $webhooks
     */
    public function webhooks(array $webhooks)
    {
        foreach ($webhooks as $name => $url) {
            $this->webhook($name, $url);
        }
    }

    /**
     * Default descriptor contents
     *
     * @return array
     */
    private function defaultContents()
    {
        return [
            'name' => config('plugin.name'),
            'description' => config('plugin.description'),
            'key' => config('plugin.key'),
            'baseUrl' => config('plugin.url'),
            'vendor' => [
                'name' => config('plugin.vendor.name'),
                'url' => config('plugin.vendor.url'),
            ],
            'version' => config('plugin.version'),
            'authentication' => [
                'type' => config('plugin.authType')
            ],
            'lifecycle' => [
                'installed' => route('installed', [], false),
                'uninstalled' => route('uninstalled', [], false),
                'enabled' => route('enabled', [], false),
                'disabled' => route('disabled', [], false)
            ],
            'scopes' => [
                'ADMIN',
                'ACT_AS_USER'
            ],
            'modules' => [
                'generalPages' => [
                    [
                        'key' => 'hello-page',
                        'url' => '/hello',
                        'name' => [
                            'value' => 'Your add-on'
                        ],
                        'icon' => [
                            'width' => 20,
                            'height' => 20,
                            'url' => asset('vendor/plugin/package.png'),
                        ]
                    ],
                ]
            ]
        ];
    }
}