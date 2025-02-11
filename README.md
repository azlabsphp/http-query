# REST Query

This library provides a client side implementation of HTTP query (similar to graphql) that allows developpers to easily customize data requested from web resources (that support the server side language implementation) and directly sending complex query parameters.

## Usage

### Query builder

The library comes with a query builder class that provides a fluent interface for building and compiling queries. An example is as follow:

- Creating a query object

```php
use Drewlabs\Query\Http\Query;

// Instruct the query object to use `http://127.0.0.1:8080` as base domain
$b = Query::new('http://127.0.0.1:8080')

            // Select the api path (path to the resource being queried)
            ->from('api/v1/examples')

            // Add a Bearer Authorization header to the request
            ->withAuthorization(BEARER_TOKEN);
```

As seen above, the API comes with numerous fluent methods for defining query intention:

- eq

The `eq` method allow developpers to build a `COLUMN=VALUE` like query:

```php
$b = $b->eq('title', 'Lorem Ipsum');
```

- neq

The `neq` is the inverse of the `eq` query method:

```php
$b = $b->neq('title', 'Lorem Ipsum');
```

- lte / lt

The `lte` and `lt` respectively allow developper to construct a query that check if a column value is less than (less than or equal to) a given value.

```php
$b = $b->lt('title', 'Lorem Ipsum')
       ->lte('title', 'Lorem Ipsum');
```

- gte / gt

The `gte` and `gt` respectively allow developper to construct a query that check if a column value is greater than (greater than or equal to) a given value.

```php
$b = $b->gt('title', 'Lorem Ipsum')
       ->gte('title', 'Lorem Ipsum');
```

- in

The `in` clause search for value that exists in a list of provided values:

```php
$b = $b->in('rates', [3, 3.5, 9]);
```

- exists

The `exists` clause allow to query for relationship existance in the database.

```php
$b = $b->exists('comments')
        ->exists('comments', new SubQuery('where', ['likes', '>', 1000]));
```

- sort

The `sort` clause allow developpers to apply a sort by `column` on the result set:

**Note** The sort order is defined by an integer value. Any value greater than `0` get converted to `ASC` while any value less than `0` is converted to `DESC`

```php
$b = $b->sort('created_at', -1); // ['sort' => ['by' => 'created_at', 'order' => 'DESC']]
```

- select

The `select` clause allows developpers to specify the list of columns to select from the query server:

```php
$b = $b->eq('title', 'Lorem Ipsum')->select('*', 'comments');
```

**Note** As shown in the examples above, `query` object provides a fluent `api` for building complex queries using PHP function calls.

### Executing composed query

After building query using the fluent API, developpers can send query requests to backend servers using the `execute` method:


```php
use Drewlabs\Query\Http\Query;

$b = Query::new('http://127.0.0.1:8080')
            ->from('api/v1/examples')
            ->withAuthorization(BEARER_TOKEN);

$b->date('created_at', '>', '2023-12-01')
    ->date('created_at', '<', '2025-02-01')
    ->exists('comments', fn($b) => $b->in('tags', [1, 4]));

// Executing the query
$result = $b->limit($limit)->execute(); // Calling execute runs the query against the HTTP api and return an instance of \Drewlabs\Query\Http\QueryResult object

print_r($result->getBody()); // returns the actual body of the query

print_r($result->first()); // return the first element of the list of items returned by the query
```

### Aggregation framework

The `query` api also support some aggregation method for performing basic mathematic computation on the resource being selected instead of returning an entire collection:

- `min(string column, string relation)`: return the minimum value of the column in the selected resource table

- `max(string column, string relation)`: return the maximum value of the column in the selected resource table

- `sum(string column, string relation)`: computes and return the sum of all values in the given column in the selected resource table

- `avg(string column, string relation)`: computes and return the average of all values in the given column in the selected resource table

- `count(string column, string relation)`: Count the total elements matching matching the provided query parameters.

**Note** All aggregation method support a second parameter allowing developpers to query a releated resource in the application database.