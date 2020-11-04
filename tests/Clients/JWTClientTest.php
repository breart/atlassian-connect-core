<?php

namespace AtlassianConnectCore\Tests\Clients;

use Illuminate\Support\Arr;

class JWTClientTest extends \AtlassianConnectCore\Tests\TestCase
{
    /**
     * @covers JWTClient::getClient
     */
    public function testSendRequestSuccess()
    {
        $expected = [
            'votingEnabled' => true,
            'watchingEnabled' => true,
            'timeTrackingConfiguration' => [
                'workingHoursPerDay' => 8.0,
                'workingDaysPerWeek' => 5.0
            ],
        ];

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($expected))
        ]);

        $actual = $client->sendRequest('get', '/rest/api/2/configuration');

        static::assertInstanceOf(\GuzzleHttp\Client::class, $client->getClient());
        static::assertEquals($actual, $expected);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\RequestException
     */
    public function testSendRequestError()
    {
        $client = $this->createClient([
            new \GuzzleHttp\Exception\RequestException(
                'Error Communicating with Server',
                new \GuzzleHttp\Psr7\Request('GET', 'test')
            )
        ]);

        $client->sendRequest('get', '/rest/api/2/configuration');
    }

    public function testGet()
    {
        $expected = ['test' => true];

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($expected))
        ]);

        $actual = $client->get('/rest/api/2/configuration');

        static::assertEquals($actual, $expected);
    }

    public function testPost()
    {
        $expected = ['test' => true];

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($expected))
        ]);

        $actual = $client->post('/rest/api/2/configuration', ['body' => 'item']);

        static::assertEquals($actual, $expected);
    }

    public function testPut()
    {
        $expected = ['test' => true];

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($expected))
        ]);

        $actual = $client->put('/rest/api/2/configuration', ['body' => 'item']);

        static::assertEmpty($actual);
    }

    public function testDelete()
    {
        $expected = ['test' => true];

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($expected))
        ]);

        $actual = $client->delete('/rest/api/2/configuration');

        static::assertEmpty($actual);
    }

    public function testSendFile()
    {
        $expected = ['test' => true];

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($expected))
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()
            ->image('avatar.png');

        $actual = $client->sendFile($file, '/rest/api/2/configuration');

        static::assertEquals($actual, $expected);
    }

    /**
     * @covers JWTClient::loadPaginator
     * @covers JWTClient::paginators
     */
    public function testPaginateWithJiraTenant()
    {
        $responses = [
            [
                'startAt' => 0,
                'maxResults' => 2,
                'total' => 4,
                'values' => [
                    [
                        'id' => 1,
                        'field' => 'value'
                    ],
                    [
                        'id' => 2,
                        'field' => 'value'
                    ]
                ],
            ],
            [
                'startAt' => 2,
                'maxResults' => 2,
                'total' => 4,
                'values' => [
                    [
                        'id' => 3,
                        'field' => 'value'
                    ],
                    [
                        'id' => 4,
                        'field' => 'value'
                    ]
                ],
            ]
        ];

        $tenant = $this->createTenant([
            'product_type' => 'jira'
        ]);

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($responses[0])),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($responses[1])),
        ], $tenant);

        $actual = $client->paginate('/rest/api/2/configuration');

        $items = Arr::collapse(Arr::pluck($responses, 'values'));

        static::assertInstanceOf(\AtlassianConnectCore\Pagination\JiraPaginator::class, $client->paginator());
        static::assertEquals($actual, $items);
    }

    /**
     * @covers JWTClient::loadPaginator
     * @covers JWTClient::paginators
     */
    public function testPaginateWithConfluenceTenant()
    {
        $responses = [
            [
                'start' => 0,
                'limit' => 2,
                'size' => 4,
                'results' => [
                    [
                        'id' => 1,
                        'field' => 'value'
                    ],
                    [
                        'id' => 2,
                        'field' => 'value'
                    ]
                ],
            ],
            [
                'start' => 2,
                'limit' => 2,
                'size' => 4,
                'results' => [
                    [
                        'id' => 3,
                        'field' => 'value'
                    ],
                    [
                        'id' => 4,
                        'field' => 'value'
                    ]
                ],
            ]
        ];

        $tenant = $this->createTenant([
            'product_type' => 'confluence'
        ]);

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($responses[0])),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($responses[1])),
        ], $tenant);

        $actual = $client->paginate('/rest/api/audit');

        $items = Arr::collapse(Arr::pluck($responses, 'results'));

        static::assertInstanceOf(\AtlassianConnectCore\Pagination\ConfluencePaginator::class, $client->paginator());
        static::assertEquals($actual, $items);
    }

    /**
     * @expectedException \Exception
     */
    public function testPaginateWithUnknown()
    {
        $tenant = $this->createTenant([
            'product_type' => 'undefined'
        ]);

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200),
        ], $tenant);

        $client->paginate('/rest/api/unknown');
    }


    public function testPaginateWithInitializedManually()
    {
        $responses = [
            [
                'startAt' => 0,
                'maxRecords' => 2,
                'total' => 4,
                'values' => [
                    [
                        'id' => 1,
                        'field' => 'value'
                    ],
                    [
                        'id' => 2,
                        'field' => 'value'
                    ]
                ],
            ],
            [
                'startAt' => 2,
                'maxRecords' => 2,
                'total' => 4,
                'values' => [
                    [
                        'id' => 3,
                        'field' => 'value'
                    ],
                    [
                        'id' => 4,
                        'field' => 'value'
                    ]
                ],
            ]
        ];

        $tenant = $this->createTenant([
            'product_type' => 'confluence'
        ]);

        $client = $this->createClient([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($responses[0])),
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($responses[1]))
        ], $tenant, new \AtlassianConnectCore\Pagination\JiraPaginator());

        // We've created Tenant for the Confluence product, but use Jira paginator
        $actual = $client->paginate('/rest/api/2/configuration');

        $items = Arr::collapse(Arr::pluck($responses, 'values'));

        static::assertInstanceOf(\AtlassianConnectCore\Pagination\JiraPaginator::class, $client->paginator());
        static::assertEquals($actual, $items);
    }

    /**
     * Creates the tenant, mocked HTTP client and JWTClient
     *
     * @param array $responses
     * @param \AtlassianConnectCore\Models\Tenant|null $tenant
     * @param \AtlassianConnectCore\Pagination\Paginator|null $paginator
     *
     * @return \AtlassianConnectCore\Http\Clients\JWTClient
     */
    protected function createClient(
        array $responses = [],
        \AtlassianConnectCore\Models\Tenant $tenant = null,
        \AtlassianConnectCore\Pagination\Paginator $paginator = null
    )
    {
        $tenant = $tenant ?? $this->createTenant();

        $mock = new \GuzzleHttp\Handler\MockHandler($responses);
        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $httpClient = new \GuzzleHttp\Client(['handler' => $handler]);

        $client = new \AtlassianConnectCore\Http\Clients\JWTClient($tenant, $paginator, $httpClient);

        return $client;
    }
}
