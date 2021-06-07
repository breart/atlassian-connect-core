<?php

namespace AtlassianConnectCore\Http\Auth;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class QSH creates a Query String Hash
 *
 * Documentation:
 * https://docs.atlassian.com/DAC/bitbucket/concepts/qsh.html
 *
 * @package App\Http\Auth
 *
 * @author Artem Brezhnev <brezzhnev@gmail.com>
 */
class QSH
{
    /**
     * The request URL.
     *
     * @var string
     */
    protected $url;

    /**
     * The request HTTP method.
     *
     * @var string
     */
    protected $method;

    /**
     * The URL parts (host, port, path...)
     *
     * @var array
     */
    protected $parts = [];

    /**
     * The list of prefixes which should be removed.
     *
     * @var array
     */
    protected $prefixes = [
        '/wiki'
    ];

    /**
     * QSH constructor.
     *
     * @param string $url
     * @param string $method
     */
    public function __construct(string $url, string $method)
    {
        $url = $this->stripBaseUrl($url);

        $this->url = $url;
        $this->parts = parse_url($url);

        $this->method = strtoupper($method);
    }

    /**
     * Create a QSH string.
     *
     * More details:
     * https://docs.atlassian.com/DAC/bitbucket/concepts/qsh.html
     *
     * @return string
     */
    public function create(): string
    {
        $parts = [
            $this->method,
            $this->canonicalUri(),
            $this->canonicalQuery()
        ];

        return hash('sha256', implode('&', $parts));
    }

    /**
     * Make a canonical URI.
     *
     * @return string|null
     */
    public function canonicalUri()
    {
        if(!$path = Arr::get($this->parts, 'path')) {
            return '/';
        }

        // Remove a prefix of instance from the path
        // Eg. remove `/wiki` part which means Confluence instance.
        $uri = $this->removePrefix($path);

        // The canonical URI should not contain & characters.
        // Therefore, any & characters should be URL-encoded to %26.
        $uri = str_replace('&', '%26', $uri);

        // The canonical URI only ends with a / character if it is the only character.
        $uri = $uri === '/'
            ? $uri
            : rtrim($uri, '/');

        return $uri;
    }

    /**
     * Make a canonical query string.
     *
     * @return string|null
     */
    public function canonicalQuery()
    {
        if(!$query = Arr::get($this->parts, 'query')) {
            return null;
        }

        $params = $this->parseQuery($query);

        // We should ignore the "JWT" parameter.
        $params = array_filter($params, function(string $key) {
            return strtolower($key) !== 'jwt';
        }, ARRAY_FILTER_USE_KEY);

        ksort($params);

        $query = $this->buildQuery($params);

        // Encode underscores.
        // $query = str_replace('_', '%20', $query);

        return $query;
    }

    /**
     * Remove a prefix from the URL path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function removePrefix(string $path): string
    {
        foreach ($this->prefixes as $prefix) {
            $pattern = '/^' . preg_quote($prefix, '/') . '/';

            if(preg_match($pattern, $path)) {
                $path = preg_replace($pattern, '', $path);

                break;
            }
        }

        return $path;
    }

    /**
     * Parse a query to array of parameters.
     *
     * @param string $query
     *
     * @return array
     */
    protected function parseQuery(string $query): array
    {
        $output = [];

        $query = ltrim($query, '?');

        $parameters = explode('&', $query);

        foreach ($parameters as $parameter) {
            list($key, $value) = array_pad(explode('=', $parameter), 2, null);

            $output = array_merge_recursive($output, [$key => $value]);
        }

        return $output;
    }

    /**
     * Build a query accordingly to RFC3986
     *
     * @param array $params
     *
     * @return string
     */
    protected function buildQuery(array $params): string
    {
        $pieces = [];

        foreach ($this->encodeQueryParams($params) as $param => $values) {
            $pieces[] = $values
                ? implode('=', [$param, implode(',', $values)])
                : $param;
        }

        return implode('&', array_filter($pieces));
    }

    /**
     * Encode query parameters.
     *
     * @param array $params
     *
     * @return array
     */
    protected function encodeQueryParams(array $params): array
    {
        $encoded = [];

        array_walk($params, function($value, string $param) use (&$encoded) {
            $key = str_replace('+', ' ', $param);
            $key = rawurlencode(rawurldecode($key));

            $values = Arr::wrap($value);
            $values = array_map(function($value) {
                $value = str_replace('+', ' ', $value);
                return rawurlencode(rawurldecode($value));
            }, $values);

            $encoded[$key] = $values;
        });

        return $encoded;
    }

    /**
     * Convert an object to a string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->create();
    }

    /**
     * @param string $url
     * @return string
     */
    private function stripBaseUrl(string $url)
    {
        if (Str::startsWith($url, config('plugin.url'))) {
            $url = Str::replaceFirst(config('plugin.url'), '', $url);
        }
        return $url;
    }
}
