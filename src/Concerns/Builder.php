<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http\Concerns;

use BadMethodCallException;
use Drewlabs\Query\Builder as QueryBuilder;
use Drewlabs\Query\Http\Utils\AggregationColumn;

trait Builder
{
    /** @var QueryBuilder */
    private $builder;

    /** @var array<string,mixed> */
    private $aggregations = [];

    /** @var string[] */
    private $aggregatedColumns = [];

    public function and($column, ?string $operator = null, $value = null)
    {
        $this->builder = $this->builder->and($column, $operator, $value);
        return $this;
    }

    public function or($column, ?string $operator = null, $value = null)
    {
        $this->builder = $this->builder->or($column, $operator, $value);
        return $this;
    }

    public function date($column, ?string $operator = null, $value = null)
    {
        $this->builder = $this->builder->date($column, $operator, $value);
        return $this;
    }

    public function orDate($column, ?string $operator = null, $value = null)
    {
        $this->builder = $this->builder->orDate($column, $operator, $value);
        return $this;
    }

    public function in(string $column, array $values)
    {
        $this->builder = $this->builder->in($column, $values);
        return $this;
    }

    public function notIn(string $column, array $values)
    {
        $this->builder = $this->builder->notIn($column, $values);
        return $this;
    }

    public function exists(string $column, $query = null)
    {
        $this->builder = $this->builder->exists($column, $query);
        return $this;
    }

    public function orExists(string $column, $query = null)
    {
        $this->builder = $this->builder->orExists($column, $query);
        return $this;
    }

    public function notExists(string $column, $query = null)
    {
        $this->builder = $this->builder->notExists($column, $query);
        return $this;
    }

    public function orNotExists(string $column, $query = null)
    {
        $this->builder = $this->builder->orNotExists($column, $query);
        return $this;
    }

    public function sort(string $column, int $order = 1)
    {
        $this->builder = $this->builder->sort($column, $order ?? 1);
        return $this;
    }

    public function isNull(string $column)
    {
        $this->builder = $this->builder->isNull($column);
        return $this;
    }

    public function orIsNull(string $column)
    {
        $this->builder = $this->builder->orIsNull($column);
        return $this;
    }

    public function notNull(string $column)
    {
        $this->builder = $this->builder->notNull($column);
        return $this;
    }

    public function orNotNull(string $column)
    {
        $this->builder = $this->builder->orNotNull($column);
        return $this;
    }

    public function between(string $column, $values)
    {
        $this->builder = $this->builder->between($column, $values);
        return $this;
    }

    public function group(string $column)
    {
        $this->builder = $this->builder->group($column);
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second)
    {
        $this->builder = $this->builder->join($table, $first, $second, $second);
        return $this;
    }

    public function limit(int $limit)
    {
        $this->builder = $this->builder->limit($limit);
        return $this;
    }

    public function select(...$columns)
    {
        $this->builder = $this->builder->select(...$columns);
        return $this;
    }


    /**
     * Add a `equals` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function eq(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '=', $value) : $this->or($column, '=', $value);
    }

    /**
     * Add a `not equals` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function neq(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '<>', $value) : $this->or($column, '<>', $value);
    }

    /**
     * Add a `less than` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function lt(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '<', $value) : $this->or($column, '<', $value);
    }

    /**
     * Add a `less than or equal to` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/and query is constructed
     *
     * @return static
     */
    public function lte(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '<=', $value) : $this->or($column, '<=', $value);
    }

    /**
     * Add a `greater than` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/where query is constructed
     *
     * @return static
     */
    public function gt(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '>', $value) : $this->or($column, '>', $value);
    }

    /**
     * Add a `greater than or equal to` clause to the query builder.
     *
     * @param mixed $value
     * @param bool  $and   Specify if an or/where query is constructed
     *
     * @return static
     */
    public function gte(string $column, $value = null, $and = true)
    {
        return $and ? $this->and($column, '>=', $value) : $this->or($column, '>=', $value);
    }

    private function aggregate(string $method, string $column, ?string $relation)
    {
        $aggregations = ['count', 'min', 'max', 'sum', 'avg'];
        if (!in_array($method = strtolower($method), $aggregations)) {
            throw new BadMethodCallException(sprintf('%s aggregation method is not supported, supported methods are %s', $method, implode(', ', $aggregations)));
        }

        $columns = empty($columns = $this->builder->getColumns()) ? ['*'] : $columns;
        $select = AggregationColumn::new($method, $column, $relation);
        array_push($this->aggregatedColumns, (string)$select);
        $params = $relation ? [$column,  $relation] : [$column];
        $this->aggregations[$method] = array_key_exists($method, $this->aggregations) ? array_merge($this->aggregations[$method] ?? [], [$params]) : [$params];
        return $this;
    }
}
