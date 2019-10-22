<?php

namespace AtlassianConnectCore\Tests\Pagination;

use AtlassianConnectCore\Pagination\Paginator;
use Illuminate\Support\Arr;

class PaginatorTest extends \AtlassianConnectCore\Tests\TestCase
{
    /**
     * @expectedException \AtlassianConnectCore\Exceptions\PaginationException
     */
    public function testValidateConfigWithoutClient()
    {
        $paginator = $this->createPaginator([
            'type' => Paginator::TYPE_PAGE,
            'url' => '/',
            'client' => null
        ]);

        iterator_to_array($paginator);
    }

    /**
     * @expectedException \AtlassianConnectCore\Exceptions\PaginationException
     */
    public function testValidateConfigWithoutURL()
    {
        $paginator = $this->createPaginator([
            'type' => Paginator::TYPE_PAGE,
            'url' => null
        ]);

        iterator_to_array($paginator);
    }

    /**
     * @expectedException \AtlassianConnectCore\Exceptions\PaginationException
     */
    public function testValidateConfigWithInvalidType()
    {
        $paginator = $this->createPaginator([
            'type' => 192,
            'url' => null
        ]);

        iterator_to_array($paginator);
    }

    /**
     * @covers Paginator::rewind
     * @covers Paginator::current
     * @covers Paginator::key
     * @covers Paginator::next
     * @covers Paginator::valid
     */
    public function testPaginateWithPageType()
    {
        $responses = [
            ['page' => 1, 'pagelen' => 1, 'values' => [['id' => 1, 'name' => 'Joe']]],
            ['page' => 2, 'pagelen' => 1, 'values' => [['id' => 2, 'name' => 'Cocker']]],
            ['page' => 3, 'pagelen' => 2, 'values' => [
                ['id' => 3, 'name' => 'Taylor'],
                ['id' => 4, 'name' => 'Otwell'],
            ]],
            ['page' => 4, 'pagelen' => 0]
        ];

        $paginator = $this->createPaginator([
            'type' => Paginator::TYPE_PAGE,
            'url' => '/',
            'offsetKey' => 'page',
            'itemsKey' => 'values',
            'perPageKey' => 'pagelen'
        ], $this->createResponses($responses));

        $expected = Arr::collapse(Arr::pluck($responses, 'values'));
        $actual = iterator_to_array($paginator);

        static::assertEquals($expected, $actual);
    }

    /**
     * @covers Paginator::isReachedTotal
     */
    public function testPaginateWithOffsetType()
    {
        $responses = [
            ['offset' => 0, 'perPage' => 2, 'total' => 5, 'items' => [['id' => 1], ['id' => 2]]],
            ['offset' => 2, 'perPage' => 2, 'total' => 5, 'items' => [['id' => 3], ['id' => 4]]],
            ['offset' => 4, 'perPage' => 2, 'total' => 5, 'items' => [['id' => 5]]],
        ];

        $paginator = $this->createPaginator([
            'type' => Paginator::TYPE_OFFSET,
            'url' => '/',
            'offsetKey' => 'offset',
            'itemsKey' => 'items',
            'perPageKey' => 'perPage',
            'totalKey' => 'total'
        ], $this->createResponses($responses));

        $expected = Arr::collapse(Arr::pluck($responses, 'items'));
        $actual = iterator_to_array($paginator);

        static::assertEquals($expected, $actual);
    }

    /**
     * @covers Paginator::getItems
     * @covers Paginator::getLastResponse
     * @covers Paginator::isNextTypeReached
     */
    public function testPaginateWithNextType()
    {
        $responses = [
            ['results' => [['id' => 1], ['id' => 2]], 'next' => '/page/2'],
            ['results' => [['id' => 3], ['id' => 4]], 'next' => '/page/3'],
            ['results' => [['id' => 5], ['id' => 6], ['id' => 7]]],
        ];

        $paginator = $this->createPaginator([
            'type' => Paginator::TYPE_NEXT,
            'url' => '/',
            'itemsKey' => 'results',
            'nextKey' => 'next'
        ], $this->createResponses($responses));

        $expected = Arr::collapse(Arr::pluck($responses, 'results'));
        $actual = iterator_to_array($paginator);

        static::assertEquals($expected, $actual);

        static::assertEquals($expected, $paginator->getItems());
        static::assertEquals(Arr::last($responses), $paginator->getLastResponse());
    }

    /**
     * Creates the tenant, mocked HTTP client and JWTClient
     *
     * @covers Paginator::setConfig
     *
     * @param array $config
     * @param array $responses
     *
     * @return Paginator
     */
    protected function createPaginator(array $config = [], array $responses = [])
    {
        if(!Arr::has($config, 'client')) {
            $mock = new \GuzzleHttp\Handler\MockHandler($responses);
            $handler = \GuzzleHttp\HandlerStack::create($mock);
            $httpClient = new \GuzzleHttp\Client(['handler' => $handler]);

            $config['client'] = $httpClient;
        }

        $paginator = new Paginator($config);

        return $paginator;
    }

    /**
     * Create array of Client responses from an array
     *
     * @param array $responses
     *
     * @return array
     */
    protected function createResponses(array $responses)
    {
        $result = [];

        foreach ($responses as $response) {
            $result[] = new \GuzzleHttp\Psr7\Response(200, [], json_encode($response));
        }

        return $result;
    }
}