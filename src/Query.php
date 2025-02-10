<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\Query\Http;

use BadMethodCallException;
use Drewlabs\Query\Builder as QueryBuilder;
use Drewlabs\Query\Http\Concerns\QueryLanguageClient;
use InvalidArgumentException;
use Drewlabs\Query\Contracts\BuilderInterface;
use Drewlabs\Query\Http\Concerns\Builder;
use Drewlabs\Query\Http\Utils\AggregationColumn;

/**
 * @method JsonResponse delete(string $id)
 * @method JsonResponse delete(int $id)
 * @method JsonResponse update(string $id, $attributes)
 * @method JsonResponse update(string $id, $attributes, array $relations)
 * @method JsonResponse update(int $id, $attributes)
 * @method JsonResponse update(int $id, $attributes, array $relations)
 * @method JsonResponse create($attributes)
 * @method JsonResponse create($attributes, array $relations)
 * @method JsonResponse get(string $id, array $columns = ['*'])
 * @method JsonResponse get(JsonBodyBuilder $query, array $columns, int $page = 1, $per_page = 100)
 * @method JsonResponse get(array $query, array $columns, int $page = 1, $per_page = 100)
 * @method JsonResponse get(JsonBodyBuilder $query, int $page = 1, $per_page = 100)
 * @method JsonResponse get(array $query, int $page = 1, $per_page = 100)
 * 
 * 
 * @package Drewlabs\Query\Http\Concerns
 */
class Query implements BuilderInterface
{
    use QueryLanguageClient;
    use Builder;

    /** @var string */
    private $host;

    /** @var string[] */
    private $uriComponents = [];

    /** @var array<string,string> */
    private $__HEADERS__ = ['content-type' => 'application/json', 'accept' => '*'];

    /**
     * Creates a class instance
     * 
     * @param string $host 
     *
     * @throws InvalidArgumentException 
     */
    public function __construct(string $host)
    {
        $this->host = $host;
        $this->uriComponents = [rtrim($host, '/')];
        $this->builder = QueryBuilder::new();
    }

    /**
     * Creates a new class instance
     * 
     * @param string $host 
     * @return Query 
     * @throws InvalidArgumentException 
     */
    public static function new(string $host)
    {
        if (false === filter_var($host, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Expect $host parameter to be a valid resource url");
        }
        return new static($host);
    }

    /**
     * overrides request content type header value
     * 
     * @param string $value 
     * @return static 
     */
    public function withContentType(string $value)
    {
        $this->__HEADERS__['content-type'] = $value;
        return $this;
    }

    public function withAuthorization(string $authToken, string $method = 'Bearer')
    {
        return $this->withAddedHeader('Authorization', trim($method) . ' ' . trim($authToken));
    }

    /**
     * Copy the current instance with a bearer token
     * 
     * @param string $token
     * 
     * @return static 
     */
    public function withBearerAuthorization(string $token)
    {
        return $this->withAddedHeader('Authorization', 'Bearer ' . $token);
    }

    /**
     * Copy the current instance with a basic authorization
     * 
     * @param string $user 
     * @param mixed $password 
     * @return Query 
     */
    public function withBasicAuthorization(string $user, $password)
    {
        return $this->withAddedHeader('Authorization', 'Basic ' . base64_encode(sprintf('%s:%s', $user, $password)));
    }

    public function from(string $path)
    {
        $this->uriComponents = [rtrim($this->host), ltrim($path, '/')];
        return $this;
    }


    public function execute()
    {
        $query = $this->builder->getRawQuery() ?? [];
        if (!empty($this->aggregations)) {
            $query['aggregate'] = $this->aggregations;
        }
        $columns = empty($columns = $this->builder->getColumns()) ? ['*'] : $columns;

        if (!empty($aggregatedColumns = $this->aggregatedColumns)) {
            $columns = array_merge($columns, $aggregatedColumns);
        }

        $body = [
            '_query' => @json_encode($query),
            '_hidden' => $this->builder->getExcludes(),
            '_columns' => array_unique($columns)
        ];

        return Client::new($this->runningTest())->sendRequest($this->getUrl(), 'GET', $body, $this->__HEADERS__)->json();
    }

    /**
     * Returns the first entry matching the query
     * 
     * @return object|null 
     * @throws BadMethodCallException 
     * @throws InvalidArgumentException 
     */
    public function first()
    {
        return $this->limit(1)->execute()->get('data.0');
    }

    /**
     * Get the count of the query result rows
     * 
     * @param array $columns
     * @param string|null $relation
     * @return int 
     * @throws BadMethodCallException 
     */
    public function count($column = '*', ?string $relation = null): int
    {
        return $this->executeAggreateQuery(__FUNCTION__, $column, $relation);
    }

    /**
     * Get the minimum value for the given column in the query result
     * 
     * @param string $column
     * @param string|null $relation
     *  
     * @return int|float 
     * @throws BadMethodCallException 
     */
    public function min(string $column, ?string $relation = null)
    {
        return $this->executeAggreateQuery(__FUNCTION__, $column, $relation);
    }

    /**
     * Get the maximum value for the given column in the query result
     * 
     * @param string $column 
     * @return int|float 
     * @throws BadMethodCallException 
     */
    public function max(string $column, ?string $relation = null)
    {
        return $this->executeAggreateQuery(__FUNCTION__, $column, $relation);
    }

    /**
     * Get the sum of all values for the given column in the query result
     * 
     * @param string $column 
     * @return int|float 
     * @throws BadMethodCallException 
     */
    public function sum(string $column, ?string $relation = null)
    {
        return $this->executeAggreateQuery(__FUNCTION__, $column, $relation);
    }

    /**
     * Get the average value for the given column in the query result
     * 
     * @param string $column 
     * @return mixed 
     * @throws BadMethodCallException 
     */
    public function avg(string $column, ?string $relation = null)
    {
        return $this->executeAggreateQuery(__FUNCTION__, $column, $relation);
    }

    /**
     * returns url to resource endpoint
     * 
     * @return string 
     */
    public function getUrl(): string
    {
        return implode('/', $this->uriComponents ?? []);
    }

    /**
     * Executes aggregation and return the aggregated result
     * 
     * @param string $method 
     * @param string $column 
     * @param null|string $relation 
     * @return mixed 
     * @throws BadMethodCallException 
     */
    private function executeAggreateQuery(string $method, string $column, ?string $relation = null)
    {
        $this->aggregations = [];
        $this->aggregatedColumns = [];
        return $this->aggregate($method, $column, $relation)->limit(1)->execute()->get(sprintf('data.0.%s', strval(AggregationColumn::new($method, $column, $relation))));
    }

    /**
     * Add a header to the request headers
     * 
     * @param string $name 
     * @param string $value 
     * @return self 
     */
    private function withAddedHeader(string $name, string $value)
    {
        $self = clone $this;
        $self->__HEADERS__[$name] = isset($self->__HEADERS__[$name]) ? array_merge($self->__HEADERS__[$name], [$value]) : [$value];
        return $self;
    }
}
