<?php

namespace AtlassianConnectCore\Tests;

/**
 * Class JWTHelperTest
 */
class JWTHelperTest extends TestCase
{
    private $realJWT = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJicmV6emhuZXYiLCJxc2giOiI5M2JkZmRmMGU5N2MwYmY1YWFkMTQ2ZTdhOWUwZmJiNWFjYzg0OGVkZTA5ZmM0ZjNlOTdlN2NjMmE5MmY0MTU2IiwiaXNzIjoiNDcyMGNkNDMtNDMyZS0zZjFkLTlhMzMtYmYxNmZkNmIzYThkIiwiY29udGV4dCI6eyJ1c2VyIjp7InVzZXJLZXkiOiJicmV6emhuZXYiLCJ1c2VybmFtZSI6ImJyZXp6aG5ldiIsImRpc3BsYXlOYW1lIjoiYnJlenpobmV2In19LCJleHAiOjE1MDIyMDM1MDMsImlhdCI6MTUwMjIwMzMyM30.qXgk5OtABoznQoY6T5Q3LJbC_7F77Efhp54jzLWkxNA';

    public function testDecode()
    {
        // Test Atlassian authentication JWT decoding
        $result = \AtlassianConnectCore\Helpers\JWTHelper::decode($this->realJWT);

        static::assertEquals([
            'header' => [
                'typ' => 'JWT',
                'alg' => 'HS256',
            ],
            'body' => [
                'sub' => 'brezzhnev',
                'qsh' => '93bdfdf0e97c0bf5aad146e7a9e0fbb5acc848ede09fc4f3e97e7cc2a92f4156',
                'iss' => '4720cd43-432e-3f1d-9a33-bf16fd6b3a8d',
                'context' => [
                    'user' => [
                        'userKey' => 'brezzhnev',
                        'username' => 'brezzhnev',
                        'displayName' => 'brezzhnev'
                    ]
                ],
                'exp' => 1502203503,
                'iat' => 1502203323,
            ],
            'signature' => 'qXgk5OtABoznQoY6T5Q3LJbC_7F77Efhp54jzLWkxNA'
        ], $result);
    }

    public function testCreate()
    {
        $iss = 'jiragit';
        $url = 'http://localhost/test';
        $method = 'get';

        $jwt = \AtlassianConnectCore\Helpers\JWTHelper::create(
            $url,
            $method,
            $iss,
            'vf7EKBf79AuaqBEthgiXIqEaEBsxYqndLFh/8VuSPeqE8flI6nJCCLRODOPwQpAXyasUm/f01/h7+diwqMdAYg'
        );

        $qsh = \AtlassianConnectCore\Helpers\JWTHelper::qsh($url, $method);

        $result = \AtlassianConnectCore\Helpers\JWTHelper::decode($jwt);

        static::assertArraySubset([
            'header' => [
                'typ' => 'JWT',
                'alg' => 'HS256',
            ],
            'body' => [
                'iss' => $iss,
                'qsh' => $qsh
            ]
        ], $result);
    }

    public function testQSH()
    {
        $qsh = \AtlassianConnectCore\Helpers\JWTHelper::qsh('http://localhost/test', 'get');

        static::assertEquals('bad328fac990349a8c88393c1755bc4f984a9c387202229b8ed52e04ff7e9fec', $qsh);
    }
}