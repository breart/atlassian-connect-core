<?php

namespace AtlassianConnectCore;

/**
 * Class Descriptor
 *
 * @package AtlassianConnectCore
 */
class Descriptor
{
    /**
     * @var array
     */
    private $contents = [];

    /**
     * Descriptor constructor.
     *
     * @param array $contents
     */
    public function __construct(array $contents = [])
    {
        $this->contents = (!$contents ? $this->defaultContents() : $contents);
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
        array_set($this->contents, $key, $value);

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
        return array_get($this->contents, $key, $default);
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