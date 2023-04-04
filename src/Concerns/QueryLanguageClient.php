<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\RestQuery\Concerns;

use BadMethodCallException;
use Drewlabs\Curl\REST\Client;
use Drewlabs\Curl\REST\Contracts\ClientInterface;
use Drewlabs\Curl\REST\Exceptions\ClientException;
use Drewlabs\Curl\REST\Exceptions\BadRequestException;
use Drewlabs\Curl\REST\Exceptions\RequestException;
use Drewlabs\Curl\REST\Testing\TestClient;
use Drewlabs\Overloadable\MethodCallExpection;
use Drewlabs\Overloadable\Overloadable;
use Drewlabs\RestQuery\JsonBodyBuilder;
use Drewlabs\RestQuery\Concerns\Testable;

trait QueryLanguageClient
{

    use Overloadable;
    use Testable;

    /**
     * @var array
     */
    private $__HEADERS__ = [];

    /**
     * 
     * @var string
     */
    private $url;

    /**
     * Creates a new client instance
     * 
     * @return ClientInterface 
     */
    private function newClient()
    {
        return $this->runningTest() ? TestClient::new() : $this->createRESTClient();
    }

    /**
     * Creates a REST client library instance
     * 
     * @return ClientInterface 
     */
    private function createRESTClient()
    {
        $client =  Client::new();
        foreach ($this->__HEADERS__ as $key => $value) {
            $client = $client->setHeader($key, $value);
        }
        return $client;
    }

    /**
     * Send a CREATE query to remote service
     * 
     * @param JsonBodyBuilder|array $attributes 
     * @param array|void $relations 
     * @return array|object 
     * 
     * @param mixed $args 
     * @return mixed 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection 
     * @throws ClientException
     * @throws BadRequestException
     * @throws RequestException
     */
    public function create(...$args)
    {
        $queryFunction = function ($attributes, array $relations = []) {
            return $this->newClient()
                ->post($this->url, array_merge($attributes instanceof JsonBodyBuilder ? $attributes->json() : $attributes, ['_query' => ['relations' => $relations]]))
                ->getBody();
        };
        return $this->overload($args, [
            function ($attributes) use (&$queryFunction) {
                return $queryFunction($attributes, []);
            },
            function ($attributes, array $relations) use (&$queryFunction) {
                return $queryFunction($attributes, $relations);
            },
        ]);
    }

    /**
     * Sends an UPDATE query to the endpoint server
     * 
     * @param string|int $id
     * @param JsonBodyBuilder|array $attributes 
     * @param array $relations 
     * @return array|object 
     * 
     * @param mixed $args 
     * @return mixed 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection 
     * @throws ClientException
     * @throws BadRequestException
     * @throws RequestException
     */
    public function update(...$args)
    {
        $queryFunction = function ($id, $attributes, $relations) {
            return $this->newClient()
                ->put(
                    sprintf("%s/%s", rtrim($this->url, '/'), strval($id)),
                    array_merge($attributes instanceof JsonBodyBuilder ? $attributes->json() : $attributes, ['_query' => ['relations' => $relations]])
                )->getBody();
        };
        return $this->overload($args, [
            function (int $id, $attributes, array $relations = []) use (&$queryFunction) {
                return $queryFunction($id, $attributes, $relations);
            },
            function (string $id, $attributes, array $relations = []) use (&$queryFunction) {
                return $queryFunction($id, $attributes, $relations);
            },
        ]);
    }

    /**
     * Send a select query to query server
     * 
     * @param mixed $args 
     * 
     * @param mixed $args 
     * @return mixed 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection 
     * @throws ClientException
     * @throws BadRequestException
     * @throws RequestException
     */
    public function select(...$args)
    {
        return $this->overload($args, [
            function (array $columns = ['*']) {
                return $this->newClient()->get($this->url, ['body' => ['_columns' => $columns ?? ['*']]])->getBody();
            },
            function (int $id, array $columns = ['*']) {
                return $this->newClient()->get(sprintf("%s/%s", rtrim($this->url ?? '', '/'), strval($id)), [
                    'body' => ['_columns' => $columns ?? ['*']]
                ])->getBody();
            },
            function (string $id, array $columns = ['*']) {
                return $this->newClient()->get(sprintf("%s/%s", rtrim($this->url ?? '', '/'), strval($id)), [
                    'body' => ['_columns' => $columns ?? ['*']]
                ])->getBody();
            },
            function (JsonBodyBuilder $query, array $columns, int $page = 1, $per_page = 100) {
                return $this->newClient()->get($this->url, [
                    'body' => array_merge($query->json(), ['_columns' => $columns]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ])->getBody();
            },
            function (array $query, array $columns, int $page = 1, $per_page = 100) {
                return $this->newClient()->get($this->url, [
                    'body' => array_merge($query, ['_columns' => $columns]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ])->getBody();
            },
            function (JsonBodyBuilder $query, int $page = 1, $per_page = 100) {
                return $this->newClient()->get($this->url, [
                    'body' => array_merge($query->json(), ['_columns' => ['*']]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ])->getBody();
            },
            function (array $query, int $page = 1, $per_page = 100) {
                return $this->newClient()->get($this->url, [
                    'body' => array_merge($query, ['_columns' => ['*']]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ])->getBody();
            }
        ]);
    }

    /**
     * Send a DELETE query to the end server
     * 
     * @param string|int $id 
     * @return array|object 
     * 
     * @param mixed $args 
     * @return mixed 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection
     * @throws ClientException
     * @throws BadRequestException
     * @throws RequestException 
     */
    public function delete(...$args)
    {
        return $this->overload($args, [
            function (int $id) {
                return $this->newClient()->delete(sprintf("%s/%s", rtrim($this->url, '/'), strval($id)))->getBody();
            },
            function (string $id) {
                return $this->newClient()->delete(sprintf("%s/%s", rtrim($this->url, '/'), strval($id)))->getBody();
            }
        ]);
    }
}
