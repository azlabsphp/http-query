<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http\Utils;

final class AggregationColumn
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
        $relation = $this->snake($this->relation);
        return !is_null($relation) ? sprintf('%s_%s%s', $relation, $this->method, $this->column === '*' ? '' : sprintf('_%s', $this->column)) : sprintf("%s_%s", $this->method, $this->column);
    }

    private function snake(string $haystack, $delimiter = '_', $escape = '\\')
    {
        if ((null === $haystack) || empty($haystack)) {
            return $haystack;
        }

        return str_replace(
            ' ',
            '',
            str_replace(
                [sprintf('%s%s', $escape, $delimiter), $escape],
                $delimiter,
                trim(strtolower(preg_replace('/([A-Z])([a-z\d])/', $delimiter . '$0', preg_replace("/[$delimiter]/", $escape, $haystack))), $delimiter)
            )
        );
    }
}
