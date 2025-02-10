<?php

namespace Drewlabs\Query\Http\Utils;

class AggregationColumn
{
    /** @var string */
    private $method;

    /** @var string */
    private $column;

    /** @var string|null */
    private $relation;

    /**
     * Column class constructor
     * 
     * @param string $method
     * @param string $column 
     * @param null|string $relation 
     * @return void
     */
    public function __construct(string $method, string $column, ?string $relation = null)
    {
        $this->method = $method;
        $this->column = $column;
        $this->relation = $relation;
    }

    /**
     * Class factory constructor
     * 
     * @param string $method 
     * @param string $column 
     * @param null|string $relation 
     * @return static 
     */
    public static function new(string $method, string $column, ?string $relation = null)
    {
        return new static($method, $column, $relation);
    }

    public function __toString(): string
    {
        return !is_null($this->relation) ? sprintf('%s_%s', $this->relation, $this->method) : sprintf("%s_%s", $this->method, $this->column);
    }
}