<?php

declare(strict_types=1);
/*
 * (c) Sidoine Azandrew <contact@liksoft.tg>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Drewlabs\RestQuery;

use Drewlabs\RestQuery\Contracts\JsonBodyBuilder;

final class QueryBuilder implements JsonBodyBuilder
{
	/**
	 * REST query value
	 * 
	 * @var array
	 */
	private $__QUERY__ = [];

	/**
	 * List of column to include in the query result
	 * 
	 * @var array
	 */
	private $__COLUMNS__ = [];

	/**
	 * List of column to exclude from the query result
	 * 
	 * @var array
	 */
	private $__EXCLUDES__ = [];


	/**
	 * Class instance factory method
	 * 
	 * @return self 
	 */
	public static function new()
	{
		return new self;
	}

	/**
	 * Add a where query filter to the builder
	 * 
	 * @param string|SubQuery|\Closure(self $builder):self $column
	 * @param string|null $operatorOrValue
	 * @param mixed|null $value
	 *
	 * @return self
	 */
	public function where($column, $operatorOrValue = null, $value = null)
	{
		$column = $column instanceof \Closure ? new SubQuery('query', $column(self::new())->getQuery()) : $column;
		$this->setWhereQuery('where', $column, $operatorOrValue, $value);
		return $this;
	}

	/**
	 * Add an or query filter to the builder
	 * 
	 * @param string|SubQuery|\Closure(self $builder):self $column
	 * @param string|null $operatorOrValue
	 * @param mixed|null $value
	 *
	 * @return self
	 */
	public function or($column, $operatorOrValue = null, $value = null)
	{
		$column = $column instanceof \Closure ? new SubQuery('query', $column(self::new())->getQuery()) : $column;
		$this->setWhereQuery('orwhere', $column, $operatorOrValue, $value);
		return $this;
	}

	/**
	 * Add a `equals` clause to the query builder
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param bool $and			Specify if an or/where query is constructed
	 *
	 * @return self
	 */
	public function eq(string $column, $value = null, $and = true)
	{
		return $and ? $this->where($column, '=', $value) : $this->or($column, '=', $value);
	}

	/**
	 * Add a `not equals` clause to the query builder
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param bool $and			Specify if an or/where query is constructed
	 *
	 * @return self
	 */
	public function neq(string $column, $value = null, $and = true)
	{
		return $and ? $this->where($column, '<>', $value) : $this->or($column, '<>', $value);
	}

	/**
	 * Add a `less than` clause to the query builder
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param bool $and			Specify if an or/where query is constructed
	 *
	 * @return self
	 */
	public function lt(string $column, $value = null, $and = true)
	{
		return $and ? $this->where($column, '<', $value) : $this->or($column, '<', $value);
	}

	/**
	 * Add a `less than or equal to` clause to the query builder
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param bool $and			Specify if an or/where query is constructed
	 *
	 * @return self
	 */
	public function lte(string $column, $value = null, $and = true)
	{
		return $and ? $this->where($column, '<=', $value) : $this->or($column, '<=', $value);
	}

	/**
	 * Add a `greater than` clause to the query builder
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param bool $and			Specify if an or/where query is constructed
	 *
	 * @return self
	 */
	public function gt(string $column, $value = null, $and = true)
	{
		return $and ? $this->where($column, '>', $value) : $this->or($column, '>', $value);
	}

	/**
	 * Add a `greater than or equal to` clause to the query builder
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param bool $and			Specify if an or/where query is constructed
	 *
	 * @return self
	 */
	public function gte(string $column, $value = null, $and = true)
	{
		return $and ? $this->where($column, '>=', $value) : $this->or($column, '>=', $value);
	}

	/**
	 * Add a `like or match` clause to the builder
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param bool $and			Specify if an or/where query is constructed
	 *
	 * @return self
	 */
	public function like(string $column, $value = null, $and = true)
	{
		return $and ? $this->where($column, 'like', $value) : $this->or($column, 'like', $value);
	}

	/**
	 * Add an `in` query filter to the builder
	 * 
	 * @param string $column
	 * @param array $values
	 * @param bool $not
	 *
	 * @return self
	 */
	public function in(string $column, array $values, bool $not = false)
	{
		# code...
		$method = $not ? 'notin' : 'in';
		if (isset($this->__QUERY__[$method])) {
			$this->__QUERY__[$method][] = [$column, $values];
		} else {
			$this->__QUERY__[$method] = [[$column, $values]];
		}
		return $this;
	}

	/**
	 * Add an `exists` subquery
	 * 
	 * **Note**
	 * The subquery is the second parameter is the subquery where the first parameter
	 * is the relation/view to query
	 * 
	 * @param string $as
	 * @param string|SubQuery|\Closure(self $builder):self $values
	 *
	 * @return self
	 */
	public function exists(string $as, $query = null)
	{
		# code...
		$query = $query instanceof \Closure ? new SubQuery('query', $query(self::new())->getQuery()) : $query;
		// Case the query is a subquery object we returns the json representation of the query
		$query = $query instanceof SubQuery ? ['column' => $as, 'match' => $query->json()] : (null === $query ? $as : [$as, $query]);
		if (isset($this->__QUERY__['has'])) {
			$this->__QUERY__['has'][] = [$query];
		} else {
			$this->__QUERY__['has'] = [$query];
		}
		return $this;
	}

	/**
	 * Add a date value to the builder
	 * 
	 * @param string|SubQuery $column 
	 * @param mixed $operator 			Operator or value parameter depending on the number of parameters passed in
	 * @param mixed $value 
	 * @param bool $and 
	 * @return $this 
	 */
	public function date($column, $operator, $value, $and = true)
	{
		$method = $and ? 'wheredate' : 'orwheredate';
		$this->setWhereQuery($method, $column, $operator, $value);
		return $this;
	}

	/**
	 * Sort query filter method
	 * 
	 * @param string $column 
	 * @param int $order 
	 * @return $this 
	 */
	public function sort(string $column, int $order = 1)
	{
		$this->__QUERY__ =  $this->__QUERY__ ?? [];
		$orderstr = intval($order) < 0 ? 'DESC' : 'ASC';
		$this->__QUERY__['sort'] = ['order' => $orderstr, 'by' => $column];
		return $this;
	}

	/**
	 * Instruct the query builder to append a count attribute
	 * named after `$as` variable to the query result
	 * 
	 * @param string $column 
	 * @param string $as
	 * 
	 * @return static 
	 */
	public function count($column = '*', string $as = null)
	{
		if (isset($this->__QUERY__['count'])) {
			$this->__QUERY__['count'][] = [null !== $as ? [$column, $as] : [$column]];
		} else {
			$this->__QUERY__['count'] = [null !== $as ? [$column, $as] : [$column]];
		}
		return $this;
	}

	/**
	 * Set the list of columns to include in the rest query result
	 * 
	 * @param mixed $columns
	 *
	 * @return self
	 */
	public function select(...$columns)
	{
		# code...
		$this->__COLUMNS__ = array_unique(array_merge($this->__COLUMNS__ ?? [], $this->flatten($columns)));
		return $this;
	}

	/**
	 * Set the list of columns to exclude from the rest query result
	 * 
	 * @param string[] $columns
	 *
	 * @return self
	 */
	public function excludes(...$columns)
	{
		# code...
		$this->__EXCLUDES__ = array_unique(array_merge($this->__EXCLUDES__ ?? [], $this->flatten($columns)));
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * 
	 *
	 * @return array|mixed
	 */
	public function json()
	{
		# code...
		return [
			'_query' => @json_encode($this->getQuery()),
			'_hidden' => $this->getExcludes(),
			'_columns' => empty($columns = $this->getColumns()) ? ['*'] : $columns
		];
	}

	/**
	 * Get __QUERY__ property value
	 * 
	 *
	 * @return array
	 */
	public function getQuery()
	{
		# code...
		return $this->__QUERY__ ?? [];
	}

	/**
	 * Get __COLUMNS__ property value
	 * 
	 *
	 * @return array
	 */
	public function getColumns()
	{
		# code...
		return $this->__COLUMNS__ ?? [];
	}

	/**
	 * Get __EXCLUDES__ property value
	 * 
	 *
	 * @return array
	 */
	public function getExcludes()
	{
		# code...
		return $this->__EXCLUDES__ ?? [];
	}

	/**
	 * Construct and set the actual where query object
	 * 
	 * @param string $method 
	 * @param mixed $column 
	 * @param mixed $operatorOrValue 
	 * @param mixed $value 
	 * @return void 
	 */
	private function setWhereQuery(string $method, $column, $operatorOrValue = null, $value = null)
	{
		$this->__QUERY__ = $this->__QUERY__ ?? [];
		$query = (!isset($operatorOrValue) && !isset($value))  ? ($column instanceof SubQuery ? $column->json() : $column) : (isset($operatorOrValue) && !isset($value) ? [$column, '=', $operatorOrValue] : [$column, $operatorOrValue, $value]);
		// Add the % prefix and suffix if query operator is a `like` or `match` query
		if (isset($query[1]) && (($query[1] === 'like') || ($query[1] === 'match')) && isset($query[2])) {
			$query[2] = false !== strpos(strval($query[2]), '%') ? $query[2] : "%" . strval($query[2]) . "%";
		}
		if (isset($this->__QUERY__[$method])) {
			$this->__QUERY__[$method][] = $query;
		} else {
			$this->__QUERY__[$method] = [$query];
		}
	}

	/**
	 * Flatten a multi-dimensional array into a single dimensional array
	 * 
	 * @param array $values 
	 * @return array 
	 */
	private function flatten(array $values)
	{
		$generator = function ($values, &$output) use (&$generator) {
			foreach ($values as $value) {
				if (is_iterable($value)) {
					$generator($value, $output);
					continue;
				}
				$output[] = $value;
			}
		};
		$out = [];
		$generator($values, $out);
		return $out;
	}
}
