<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\Query\Http;

use Drewlabs\Query\Http\Concerns\QueryLanguageClient;
use InvalidArgumentException;
// use Drewlabs\Query\Builder;
use Drewlabs\Query\Utils\SubQuery;

/**
 * @method JsonResponse delete(string $id)
 * @method JsonResponse delete(int $id)
 * @method JsonResponse update(string $id, $attributes)
 * @method JsonResponse update(string $id, $attributes, array $relations)
 * @method JsonResponse update(int $id, $attributes)
 * @method JsonResponse update(int $id, $attributes, array $relations)
 * @method JsonResponse create($attributes)
 * @method JsonResponse create($attributes, array $relations)
 * @method JsonResponse select(string $id, array $columns = ['*'])
 * @method JsonResponse select(JsonBodyBuilder $query, array $columns, int $page = 1, $per_page = 100)
 * @method JsonResponse select(array $query, array $columns, int $page = 1, $per_page = 100)
 * @method JsonResponse select(JsonBodyBuilder $query, int $page = 1, $per_page = 100)
 * @method JsonResponse select(array $query, int $page = 1, $per_page = 100)
 * 
 * 
 * @method static and($column, ?string $operator = null, string|SubQuery|\Closure $value = null)
 * @method static or($column, $operator = null, string|SubQuery|\Closure $value = null)
 * @method static eq(string $column, $value = null, $and = true)
 * @method static neq(string $column, $value = null, $and = true)
 * @method static lt(string $column, $value = null, $and = true)
 * @method static lte(string $column, $value = null, $and = true)
 * @method static gt(string $column, $value = null, $and = true)
 * @method static gte(string $column, $value = null, $and = true)
 * @method static like(string $column, $value = null, $and = true)
 * @method static date($column, ?string $operator = null, $value = null)
 * @method static orDate($column, ?string $operator = null, $value = null)
 * @method static in(string $column, array $values)
 * @method static notIn(string $column, array $values)
 * @method static exists(string $as, $query = null)
 * @method static orExists(string $column, $query = null)
 * @method static notExists(string $column, $query = null)
 * @method static orNotExists(string $column, $query = null)
 * @method static sort(string $column, int $order = 1)
 * 
 * @package Drewlabs\Query\Http\Concerns
 */
class Query
{
    use QueryLanguageClient;

    private $builder;

    /**
     * Creates a class instance
     * 
     * @param string $host 
     *
     * @throws InvalidArgumentException 
     */
    public function __construct(string $host)
    {
        $this->url = $host;
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
        $this->__HEADERS__['Content-Type'] = $value;
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
