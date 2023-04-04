<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\RestQuery;

use Drewlabs\RestQuery\Concerns\QueryLanguageClient;
use InvalidArgumentException;

/**
 * @method bool|int delete(string $id)
 * @method bool|int delete(int $id)
 * @method mixed update(string $id, $attributes)
 * @method mixed update(string $id, $attributes, array $relations)
 * @method mixed update(int $id, $attributes)
 * @method mixed update(int $id, $attributes, array $relations)
 * @method mixed create($attributes)
 * @method mixed create($attributes, array $relations)
 * @method mixed select(string $id, array $columns = ['*'])
 * @method array|mixed select(JsonBodyBuilder $query, array $columns, int $page = 1, $per_page = 100)
 * @method array|mixed select(array $query, array $columns, int $page = 1, $per_page = 100)
 * @method array|mixed select(JsonBodyBuilder $query, int $page = 1, $per_page = 100)
 * @method array|mixed select(array $query, int $page = 1, $per_page = 100)
 * 
 * @package Drewlabs\RestQuery
 */
class Client
{
    use QueryLanguageClient;

    /**
     * Creates a class instance
     * 
     * @param string $url 
     *
     * @throws InvalidArgumentException 
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * Creates a new class instance
     * 
     * @param string $url 
     * @return Client 
     * @throws InvalidArgumentException 
     */
    public static function new(string $url)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Expect $url parameter to be a valid resource url");
        }
        return new self($url);

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
     * @return Client 
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
