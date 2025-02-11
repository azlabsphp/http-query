<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http\Validation;

use Drewlabs\Query\Http\Query;

/**
 * @property Query $builder
 */
trait HasBuilder
{
    /**
     * Authorize the existance HTTP request with the token.
     *
     * @return static
     */
    public function withAuthorization(string $authToken, string $method = 'Bearer')
    {
        $this->builder = $this->builder->withAuthorization($authToken, $method);
        return $this;
    }

    public function where($column, $value = null)
    {
        $this->builder = $this->builder->and($column, !is_null($value) ? '=' : null, $value);
        return $this;
    }

    public function whereNot($column, $value = null)
    {
        $this->builder = $this->builder->and($column, !is_null($value) ? '<>' : null, $value);
        return $this;
    }

    public function whereNotNull(string $column)
    {
        $this->builder = $this->builder->notNull($column);
        return $this;
    }

    public function whereNull(string $column)
    {
        $this->builder = $this->builder->isNull($column);
        return $this;
    }
}
