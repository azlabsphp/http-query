<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\Query\Http\Concerns;

use BadMethodCallException;
use Drewlabs\Overloadable\MethodCallExpection;
use Drewlabs\Overloadable\Overloadable;
use Drewlabs\Query\Http\Client;
use Drewlabs\Query\Http\Contracts\JsonBodyBuilder;
use Drewlabs\Query\Http\Testing\Testable;
use Drewlabs\Query\Http\QueryResult;


/**
 * @method string getUrl()
 * @method array getHeaders()
 */
trait RESTClient
{
    use Overloadable;
    use Testable;


    /**
     * Send a CREATE query to remote service
     * 
     * @param mixed ...$args 
     * @return QueryResult 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection 
     */
    public function create(...$args)
    {
        $fn = function (array $attributes, array $relations = []) {
            return Client::new($this->runningTest())
                ->sendRequest(
                    $this->getUrl(),
                    'POST',
                    array_merge($attributes, ['_query' => ['relations' => $relations]]),
                    $this->getHeaders()
                )
                ->json();
        };
        return $this->overload($args, [
            function (array $attributes, ?array $relations = null) use (&$fn) {
                return $fn($attributes, $relations ?? []);
            },
            function (JsonBodyBuilder $attributes, ?array $relations = null) use (&$fn) {
                return $fn($attributes->json(), $relations ?? []);
            }
        ]);
    }

    /**
     * Sends an UPDATE query to the endpoint server
     * 
     * @param mixed ...$args 
     * @return QueryResult 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection 
     */
    public function update(...$args)
    {
        $fn = function ($id, $attributes, $relations) {
            return Client::new($this->runningTest())
                ->sendRequest(
                    sprintf("%s/%s", rtrim($this->getUrl(), '/'), $id),
                    'PUT',
                    array_merge($attributes, ['_query' => ['relations' => $relations]]),
                    $this->getHeaders()
                )->json();
        };
        return $this->overload($args, [
            function (int $id, array $attributes, ?array $relations = null) use (&$fn) {
                return $fn(strval($id), $attributes, $relations ?? []);
            },
            function (string $id, array $attributes, ?array $relations = null) use (&$fn) {
                return $fn($id, $attributes, $relations ?? []);
            },
            function (int $id, JsonBodyBuilder $attributes, ?array $relations = null) use (&$fn) {
                return $fn(strval($id), $attributes, $relations ?? []);
            },
            function (string $id, JsonBodyBuilder $attributes, ?array $relations = null) use (&$fn) {
                return $fn($id, $attributes, $relations ?? []);
            },
        ]);
    }

    /**
     * Send a select query to query server
     * 
     * @param mixed ...$args 
     * @return QueryResult 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection 
     */
    public function get(...$args)
    {
        $fn = function (string $url, array $body) {
            return Client::new($this->runningTest())
                ->sendRequest(
                    $url,
                    'GET',
                    $body,
                    $this->getHeaders()
                )->json();
        };
        return $this->overload($args, [
            function (array $columns = ['*']) use (&$fn) {
                return $fn($this->getUrl(), ['_columns' => $columns ?? ['*']]);
            },
            function (int $id, array $columns = ['*']) use (&$fn) {
                return $fn(sprintf("%s/%s", rtrim($this->getUrl() ?? '', '/'), strval($id)), ['_columns' => $columns ?? ['*']]);
            },
            function (string $id, array $columns = ['*']) use (&$fn) {
                return $fn(sprintf("%s/%s", rtrim($this->getUrl() ?? '', '/'), strval($id)), ['_columns' => $columns ?? ['*']]);
            },
            function (JsonBodyBuilder $query, array $columns, ?int $page = null, $per_page = null) use (&$fn) {
                return $fn($this->buildQueryUri($this->getUrl(), ['page' => $page, 'per_page' => $per_page]), array_merge(['_query' => $query->json()], ['_columns' => $columns]));
            },
            function (array $query, array $columns, ?int $page = null, $per_page = null) use (&$fn) {
                return $fn($this->buildQueryUri($this->getUrl(), ['page' => $page, 'per_page' => $per_page]), array_merge(['_query' => $query], ['_columns' => $columns]));
            },
            function (JsonBodyBuilder $query, ?int $page = null, $per_page = null) use (&$fn) {
                return $fn($this->buildQueryUri($this->getUrl(), ['page' => $page, 'per_page' => $per_page]), array_merge(['_query' => $query->json()], ['_columns' => ['*']]));
            },
            function (array $query, ?int $page = null, $per_page = null) use (&$fn) {
                return $fn($this->buildQueryUri($this->getUrl(), ['page' => $page, 'per_page' => $per_page]), array_merge(['_query' => $query], ['_columns' => ['*']]));
            }
        ]);
    }

    /**
     * Send a DELETE query to the end server
     * 
     * @param mixed ...$args 
     * @return QueryResult 
     * @throws BadMethodCallException 
     * @throws MethodCallExpection 
     */
    public function delete(...$args)
    {

        $fn = function (string $url) {
            return Client::new($this->runningTest())
                ->sendRequest(
                    $url,
                    'DELETE',
                    [],
                    $this->getHeaders()
                )->json();
        };
        return $this->overload($args, [
            function (int $id) use (&$fn) {
                return $fn(sprintf("%s/%s", rtrim($this->getUrl(), '/'), strval($id)));
            },
            function (string $id) use (&$fn) {
                return $fn(sprintf("%s/%s", rtrim($this->getUrl(), '/'), $id));
            }
        ]);
    }

    /**
     * Construct an http url with query parameters based on provided parameters
     * 
     * @param string $url 
     * @param array $query 
     * @return string 
     */
    private function buildQueryUri(string $url, array $query = []): string
    {
        $p = mb_strpos($url, '?') === false ? '?' : '&';
        return sprintf('%s%s%s', $url, $p, http_build_query(array_filter($query, function ($item) {
            return !is_null($item);
        })));
    }
}
