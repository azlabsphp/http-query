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
use Drewlabs\Query\Http\JsonResponse;


/**
 * @method string getUrl()
 * @method array getHeaders()
 */
trait QueryLanguageClient
{
    use Overloadable;
    use Testable;


    /**
     * Send a CREATE query to remote service
     * 
     * @param mixed ...$args 
     * @return JsonResponse 
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
     * @return JsonResponse 
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
     * @return JsonResponse 
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
                return $fn($this->getUrl(), ['body' => ['_columns' => $columns ?? ['*']]]);
            },
            function (int $id, array $columns = ['*']) use (&$fn) {
                return $fn(sprintf("%s/%s", rtrim($this->getUrl() ?? '', '/'), strval($id)), [
                    'body' => ['_columns' => $columns ?? ['*']]
                ]);
            },
            function (string $id, array $columns = ['*']) use (&$fn) {
                return $fn(sprintf("%s/%s", rtrim($this->getUrl() ?? '', '/'), strval($id)), [
                    'body' => ['_columns' => $columns ?? ['*']]
                ]);
            },
            function (JsonBodyBuilder $query, array $columns, int $page = 1, $per_page = 100) use (&$fn) {
                return $fn($this->getUrl(), [
                    'body' => array_merge($query->json(), ['_columns' => $columns]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ]);
            },
            function (array $query, array $columns, int $page = 1, $per_page = 100) use (&$fn) {
                return $fn($this->getUrl(), [
                    'body' => array_merge($query, ['_columns' => $columns]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ]);
            },
            function (JsonBodyBuilder $query, int $page = 1, $per_page = 100) use (&$fn) {
                return $fn($this->getUrl(), [
                    'body' => array_merge($query->json(), ['_columns' => ['*']]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ]);
            },
            function (array $query, int $page = 1, $per_page = 100) use (&$fn) {
                return $fn($this->getUrl(), [
                    'body' => array_merge($query, ['_columns' => ['*']]),
                    'query' => ['page' => $page ?? 1, 'per_page' => $per_page ?? 100]
                ]);
            }
        ]);
    }

    /**
     * Send a DELETE query to the end server
     * 
     * @param mixed ...$args 
     * @return JsonResponse 
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
}
