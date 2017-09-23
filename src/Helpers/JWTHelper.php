<?php

namespace AtlassianConnectCore\Helpers;

/**
 * Class JWTHelper
 *
 * @package AtlassianConnectCore\Helpers
 */
class JWTHelper
{
    /**
     * Decode JWT token
     *
     * @param string $token
     *
     * @return array|null
     */
    public static function decode($token)
    {
        $parts = explode('.', $token);

        if(count($parts) !== 3) {
            return null;
        }

        return [
            'header' => json_decode(base64_decode($parts[0]), true),
            'body' => json_decode(base64_decode($parts[1]), true),
            'signature' => $parts[2]
        ];
    }

    /**
     * Create JWT token used by Atlassian REST API request
     *
     * @param string $url URL of the request
     * @param string $method HTTP method
     * @param string $issuer Key of the add-on
     * @param string $secret Shared secret of the Tenant
     *
     * @return string
     */
    public static function create(string $url, string $method, string $issuer, string $secret)
    {
        $payload = [
            'iss' => $issuer,
            'iat' => time(),
            'exp' => time() + 86400,
            'qsh' => static::qsh($url, $method)
        ];

        return \Firebase\JWT\JWT::encode($payload, $secret);
    }

    /**
     * Create Query String Hash
     *
     * More details:
     * https://developer.atlassian.com/static/connect/docs/latest/concepts/understanding-jwt.html#creating-token
     *
     * @param string $url URL of the request
     * @param string $method HTTP method
     *
     * @return string
     */
    public static function qsh($url, $method)
    {
        $method = strtoupper($method);
        $parts = parse_url($url);

        // Remove "/wiki" part from the path for the Confluence
        // Really, I didn't find this part in the docs, but it works
        $path = str_replace('/wiki', '', $parts['path']);

        $canonicalQuery = '';

        if (!empty($parts['query'])) {
            $query = $parts['query'];
            $queryParts = explode('&', $query);
            $queryArray = [];

            foreach ($queryParts as $queryPart) {
                $pieces = explode('=', $queryPart);
                $key = array_shift($pieces);
                $key = rawurlencode($key);
                $value = substr($queryPart, strlen($key) + 1);
                $value = rawurlencode($value);
                $queryArray[$key][] = $value;
            }

            ksort($queryArray);

            foreach ($queryArray as $key => $pieceOfQuery) {
                $pieceOfQuery = implode(',', $pieceOfQuery);
                $canonicalQuery .= $key . '=' . $pieceOfQuery . '&';
            }

            $canonicalQuery = rtrim($canonicalQuery, '&');
        }

        $qshString = implode('&', [$method, $path, $canonicalQuery]);
        $qsh = hash('sha256', $qshString);

        return $qsh;
    }

    /**
     * JWT Authentication middleware for Guzzle
     *
     * @param string $issuer Add-on key in most cases
     * @param string $secret Shared secret
     *
     * @return callable
     */
    public static function authTokenMiddleware(string $issuer, string $secret)
    {
        return \GuzzleHttp\Middleware::mapRequest(
            function (\Psr\Http\Message\RequestInterface $request)
            use ($issuer, $secret)
        {
            // Generate token
            $token = static::create(
                (string) $request->getUri(),
                $request->getMethod(),
                $issuer,
                $secret
            );

            return new \GuzzleHttp\Psr7\Request(
                $request->getMethod(),
                $request->getUri(),
                array_merge($request->getHeaders(), ['Authorization' => 'JWT ' . $token]),
                $request->getBody()
            );
        });
    }
}