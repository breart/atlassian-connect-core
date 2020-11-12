<?php

namespace AtlassianConnectCore\Http\Clients;

use GuzzleHttp\Client;
use AtlassianConnectCore\Models\Tenant;
use AtlassianConnectCore\Helpers\JWTHelper;
use AtlassianConnectCore\Pagination\Paginator;
use Illuminate\Support\Arr;

/**
 * Class JWTClient
 *
 * @package AtlassianConnectCore\Http\Clients
 */
class JWTClient
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Tenant
     */
    protected $tenant;

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * JWTRequest constructor.
     *
     * @param Tenant $tenant
     * @param Paginator $paginator
     * @param Client $client HTTP Client
     */
    public function __construct(Tenant $tenant, Paginator $paginator = null, Client $client = null)
    {
        $this->tenant = $tenant;
        $this->paginator = $paginator;
        $this->client = $client ?? $this->createClient();
    }

    /**
     * Create a HTTP client
     *
     * @return Client
     */
    private function createClient()
    {
        $stack = new \GuzzleHttp\HandlerStack();
        $stack->setHandler(new \GuzzleHttp\Handler\CurlHandler());

        $stack->push(JWTHelper::authTokenMiddleware(
            $this->tenant->addon_key,
            $this->tenant->shared_secret
        ));

        return new Client(['handler' => $stack]);
    }

    /**
     * Send a request to the instance
     *
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param array $config HTTP Client config
     * @param bool $paginate Where request is paginated
     *
     * @return array|string
     */
    public function sendRequest(string $method = 'get', string $url, array $config = [], bool $paginate = false)
    {
        // If URL has host we shouldn't use tenant baseUrl
        $baseUrl = preg_match('/^https?\:\/\//', $url) ? null : $this->tenant->base_url;

        // If base url contains path, Guzzle client will truncate it
        // So we need to keep it and append to the request URL
        $baseUrlPath = parse_url($baseUrl, PHP_URL_PATH);
        $url = rtrim($baseUrlPath, '/') . '/' . ltrim($url, '/');

        $clientConfig = array_merge(['base_uri' => $baseUrl], $config);

        if($paginate && $this->paginator instanceof Paginator) {
            $this->paginator->setConfig([
                'url' => $url,
                'client' => $this->client,
                'clientConfig' => $clientConfig
            ]);

            return iterator_to_array($this->paginator);
        }

        $response = $this->client->request($method, $url, $clientConfig);

        $contents = $response->getBody()
            ->getContents();

        if($contents && $decoded = json_decode($contents, true)) {
            return $decoded;
        }

        return $contents;
    }

    /**
     * Send a GET request
     *
     * @param string $url Request URL
     * @param array $config HTTP client config
     *
     * @return array|string
     */
    public function get(string $url, array $config = [])
    {
        return $this->sendRequest('get', $url, $config);
    }

    /**
     * Send a POST request
     *
     * @param string $url Request URL
     * @param array $body Request body params
     * @param array $config HTTP client config
     *
     * @return array|string
     */
    public function post(string $url, array $body = [], array $config = [])
    {
        return $this->sendRequest('post', $url, array_merge($config, [
            'json' => $body
        ]));
    }

    /**
     * Send a PUT request
     *
     * @param string $url Request URL
     * @param array $body Request body params
     * @param array $config HTTP client config
     *
     * @return array|string
     */
    public function put(string $url, array $body = [], array $config = [])
    {
        $this->sendRequest('put', $url, array_merge($config, [
            'json' => $body
        ]));
    }

    /**
     * Send a DELETE request
     *
     * @param string $url Request URL
     * @param array $config HTTP client config
     *
     * @return array|string
     */
    public function delete(string $url, array $config = [])
    {
        $this->sendRequest('delete', $url, $config);
    }

    /**
     * Paginate a request
     *
     * @param string $url Request URL
     * @param array $config HTTP client config
     * @param array $paginatorConfig Paginator config
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function paginate(string $url, array $config = [], array $paginatorConfig = []): array
    {
        $this->loadPaginator($paginatorConfig);

        return $this->sendRequest('get', $url, $config, true);
    }

    /**
     * Send a file
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $url Request URL
     * @param array $config HTTP client config
     *
     * @return array|string
     */
    public function sendFile(\Illuminate\Http\UploadedFile $file, string $url, array $config = [])
    {
        // Save file to the temporary folder
        $stored = $file->move('/tmp/', $file->getClientOriginalName());

        $resource = fopen($stored->getRealPath(), 'r');

        unlink($stored->getRealPath());

        return $this->sendRequest('post', $url, array_merge($config, [
            'headers' => ['X-Atlassian-Token' => 'nocheck'],
            'multipart' => [[
                'name' => 'file',
                'contents' => $resource
            ]]
        ]));
    }

    /**
     * Returns HTTP client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns paginator
     *
     * @return Paginator
     */
    public function paginator()
    {
        return $this->paginator;
    }

    /**
     * Instantiate paginator class or apply config to existing
     *
     * @param array $config
     *
     * @throws \Exception
     */
    private function loadPaginator(array $config = [])
    {
        /*if($this->paginator) {
            $this->paginator->setConfig($config);

            return;
        }*/

        $alias = $this->tenant->product_type;

        if(!$paginatorClass = Arr::get($this->paginators(), $alias)) {
            throw new \Exception('Class for the paginator alias "' . $alias . '" could not be found');
        }

        $this->paginator = new $paginatorClass($config);
    }

    /**
     * The paginators with aliases
     *
     * @return array
     */
    private function paginators(): array
    {
        return [
            'jira' => \AtlassianConnectCore\Pagination\JiraPaginator::class,
            'confluence' => \AtlassianConnectCore\Pagination\ConfluencePaginator::class
        ];
    }
}
