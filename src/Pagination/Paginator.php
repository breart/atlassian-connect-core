<?php

namespace AtlassianConnectCore\Pagination;

use GuzzleHttp\Client;
use AtlassianConnectCore\Exceptions\PaginationException;
use Illuminate\Support\Arr;

/**
 * Class Paginator
 *
 * @package AtlassianConnectCore\Pagination
 */
class Paginator implements \Iterator
{
    /** Number page presents */
    const TYPE_PAGE = 1;

    /** Offset number of items presents */
    const TYPE_OFFSET = 2;

    /** Next page link presents in the response */
    const TYPE_NEXT = 3;

    /**
     * Type of pagination
     *
     * @var int
     */
    protected $type;

    /**
     * URL of the request
     *
     * @var string
     */
    protected $url;

    /**
     * HTTP client
     *
     * @var Client
     */
    protected $client;

    /**
     * HTTP client config
     *
     * @var array
     */
    protected $clientConfig = [];

    /**
     * The name of key representing limitation of results per page
     *
     * @var string
     */
    protected $perPageKey;

    /**
     * The name of key representing offset
     *
     * @var string
     */
    protected $offsetKey;

    /**
     * The name of key representing total items count
     *
     * @var string
     */
    protected $totalKey;

    /**
     * The name of key representing container of items
     *
     * @var string
     */
    protected $itemsKey;

    /**
     * The name of key representing next page link
     *
     * @var string
     */
    protected $nextKey;

    /**
     * The number of results per page
     *
     * @var int
     */
    protected $perPage;

    /**
     * The number of current offset position
     *
     * @var int
     */
    protected $offset;

    /**
     * The number of total items should be fetched
     *
     * @var string
     */
    protected $total;

    /**
     * The items collected from responses
     *
     * @var array
     */
    protected $items = [];

    /**
     * The position of the iterator
     *
     * @var int
     */
    protected $position;

    /**
     * The list of hashes of fetched responses for preventing duplicated contents
     *
     * @var array
     */
    private $hashedResponses = [];

    /**
     * List of fillable properties
     *
     * @var array
     */
    private $fillable = [
        'type',
        'url',
        'client',
        'clientConfig',
        'perPageKey',
        'offsetKey',
        'totalKey',
        'itemsKey',
        'nextKey',
        'total',
        'perPage',
        'offset'
    ];

    /**
     * Number of fetched items in the last iteration
     *
     * @var int
     */
    private $fetchedCount = 0;

    /**
     * Debugging information
     *
     * @var array
     */
    private $dump;

    /**
     * Last response contents
     *
     * @var array
     */
    private $lastResponse = [];

    /**
     * Paginator constructor.
     *
     * @param array $config Config params
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Apply config params
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        foreach ($config as $item => $value)
        {
            if(!in_array($item, $this->fillable)) {
                throw new PaginationException('Property `' . $item . '` should be fillable');
            }

            if(!property_exists($this, $item)) {
                throw new PaginationException('Property `' . $item . '` should be defined');
            }

            $this->{$item} = $value;
        }
    }

    /**
     * Validate config params
     */
    public function validateConfig()
    {
        if(!$this->type || !in_array($this->type, [self::TYPE_PAGE, self::TYPE_OFFSET, self::TYPE_NEXT])) {
            throw new PaginationException('Pagination type is undefined or invalid');
        }

        if(!$this->client) {
            throw new PaginationException('HTTP Client should be defined');
        }

        if(!$this->url) {
            throw new PaginationException('Request URL should be defined');
        }
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get last response
     *
     * @return array
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;

        // Offset starts from 1 for "Page" type of pagination
        $this->offset = $this->offset ?? ($this->type === self::TYPE_PAGE ? 1 : 0);

        // Prepare for the first fetching
        $this->prepareNextPage();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        $isFetched = array_key_exists($this->position, $this->items);

        // Check for reaching ends, firstly we need to check the equality between total value and current position
        // For the NEXT type, next link should have appeared in the response
        $isReached = $this->isReachedTotal() || $this->isNextTypeReached();

        // Valid until total number not reached, requested item already fetched or response isn't absent
        return $isFetched || (!$isReached && count($this->fetchPage($this->position)) > 0);
    }

    /**
     * Whether position is reached to total number of items
     *
     * @return bool
     */
    protected function isReachedTotal()
    {
        if($this->total === null) {
            return false;
        }

        return $this->position === $this->total;
    }

    /**
     * Whether position is reached of the NEXT type of pagination
     *
     * @return bool
     */
    protected function isNextTypeReached()
    {
        if($this->type !== self::TYPE_NEXT || $this->position === 0) {
            return false;
        }

        return !Arr::has($this->lastResponse, $this->nextKey);
    }

    /**
     * Fetch a page and retrieve items
     *
     * @param int $position Position of iteration (item number)
     *
     * @return array Fetched items
     */
    protected function fetchPage($position): array
    {
        if(array_key_exists($position, $this->items)) {
            return $this->items[$position];
        }

        $this->validateConfig();

        $this->fetchedCount = 0;

        $response = $this->sendRequest($this->url, $this->clientConfig);

        $this->lastResponse = $response;

        // If duplicated response exist it means that forever loop there is a place to be
        // So we need to abort further fetches
        if($this->preventDuplicatedResponse()) {
            return [];
        }

        $items = Arr::get($response, $this->itemsKey, []);

        $this->fetchedCount = count($items);

        $this->items = array_merge($this->items, $items);

        $this->grabTotalCount();

        $this->prepareNextPage();

        return $items;
    }

    /**
     * Grab total count value from response
     */
    protected function grabTotalCount()
    {
        if($this->total !== null || !$this->totalKey) {
            return;
        }

        $this->total = Arr::get($this->lastResponse, $this->totalKey);
    }

    /**
     * Prepare paginator for the next page fetching
     */
    protected function prepareNextPage()
    {
        $this->increment();

        if($this->type === self::TYPE_NEXT) {

            // Check for "next" key containing next page URL
            if(strlen($this->nextKey) && Arr::has($this->lastResponse, $this->nextKey)) {
                $this->url = Arr::get($this->lastResponse, $this->nextKey);
                $this->clientConfig = $this->mergeClientConfig(['query' => $this->extractQueryParams($this->url)]);
            }
        }
        else {

            // For other types of pagination we just increment offset key
            $params = ['query' => [
                $this->perPageKey => $this->perPage,
                $this->offsetKey => $this->offset
            ]];
        }

        $this->clientConfig = $this->mergeClientConfig($params ?? []);
    }

    /**
     * Send a request and return contents
     *
     * @param string $url Request URL
     * @param array $config Client config
     *
     * @return array
     */
    protected function sendRequest(string $url, array $config): array
    {
        $response = $this->client->get($url, $config);

        $contents = $response
            ->getBody()
            ->getContents();

        return \GuzzleHttp\json_decode($contents, true);
    }

    /**
     * Increment values for the next page fetching
     */
    protected function increment()
    {
        if($this->type === self::TYPE_OFFSET) {
            $this->offset += $this->fetchedCount;
        }
        else {
            $this->offset++;
        }
    }

    /**
     * Add request params by merge
     *
     * @param array $params
     *
     * @return array
     */
    protected function mergeClientConfig(array $params): array
    {
        return array_merge($this->clientConfig, $params);
    }

    /**
     * Check for duplicated response
     *
     * @return bool Whether response is duplicated
     */
    protected function preventDuplicatedResponse()
    {
        $hash = $this->hashResponse($this->lastResponse);

        if(in_array($hash, $this->hashedResponses)) {
            return true;
        }

        $this->hashedResponses[] = $hash;

        return false;
    }

    /**
     * Hash a response
     *
     * @param mixed $response
     *
     * @return int
     */
    protected function hashResponse($response)
    {
        if(!is_string($response)) {
            $response = json_encode($response);
        }

        return md5($response);
    }

    /**
     * Extract query params from the URL to an associative array
     *
     * @param string $url
     *
     * @return array
     */
    protected function extractQueryParams(string $url): array
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);

        return $params;
    }
}