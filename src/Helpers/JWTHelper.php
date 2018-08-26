<?php

namespace AtlassianConnectCore\Helpers;

use AtlassianConnectCore\Http\Auth\QSH;

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
            'qsh' => new QSH($url, $method)
        ];

        return \Firebase\JWT\JWT::encode($payload, $secret);
    }

    /**
     * Create Query String Hash
     *
     * More details:
     * https://docs.atlassian.com/DAC/bitbucket/concepts/qsh.html
     *
     * @param string $url URL of the request
     * @param string $method HTTP method
     *
     * @return string
     */
    public static function qsh($url, $method): string
    {
        return new QSH($url, $method);
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