# REST Query

This library provides a client side implementation of RESTful services query (similar to graphql) that allows developpers to easily customize data requested from web resources and directly sending complex query parameters to REST service.

**Requirements**
The library only works with compatible web services. Therefore, developpers must provides an implementation that parses, compiles and process query send to the service.

## Installation

Using composer PHP package manager:

> composer require drewlabs/rest-query

## Usage

### Query builder

The library comes with a query builder class that provides a fluent interface for building and compiling queries. An example is as follow:

```php
use Drewlabs\RestQuery\QueryBuilder;

// ...


// Building the query and using the query builder fluent API
$builder = QueryBuilder::new()
            ->eq('title', 'Lorem Ipsum')
            ->neq('id', 10)
            ->where(function (QueryBuilder $builder) {
                return $builder->in('tags', ['I', 'L', 'F'])
                    ->gt('likes', 120)
                    ->gte('groups', 10);
            });

// Compiling the query output to JSON string
$result = $builder->json();
```

As seen above, the API comes with numerous fluent methods for defining query intention:

- eq

The `eq` method allow developpers to build a `COLUMN=VALUE` like query:

```php
$result = QueryBuilder::new()->eq('title', 'Lorem Ipsum')->getQuery(); // ['where' => [['title, '=', 'Lorem Ipsum']]]
```

- neq

The `neq` is the inverse of the `eq` query method:

```php
$result = QueryBuilder::new()->neq('title', 'Lorem Ipsum')->getQuery(); // ['where' => [['title, '<>, 'Lorem Ipsum']]]
```

- lte / lt

The `lte` and `lt` respectively allow developper to construct a query that check if a column value is less than (less than or equal to) a given value.

```php
$result = QueryBuilder::new()->lt('title', 'Lorem Ipsum')->getQuery(); // ['where' => [['title, '<', 'Lorem Ipsum']]]
$result = QueryBuilder::new()->lte('title', 'Lorem Ipsum')->getQuery(); // ['where' => [['title, '<=', 'Lorem Ipsum']]]
```

- gte / gt

The `gte` and `gt` respectively allow developper to construct a query that check if a column value is greater than (greater than or equal to) a given value.

```php
$result = QueryBuilder::new()->gt('title', 'Lorem Ipsum')->getQuery(); // ['where' => [['title, '>', 'Lorem Ipsum']]]
$result = QueryBuilder::new()->gte('title', 'Lorem Ipsum')->getQuery(); // ['where' => [['title, '>=', 'Lorem Ipsum']]]
```

- in

The `in` clause search for value that exists in a list of provided values:

```php
$result = QueryBuilder::new()->in('rates', [3, 3.5, 9])->getQuery(); // ['in' => [['title, [3, 3.5, 9]]]]
```

- exists

The `exists` clause allow to query for relationship existance in the database.

```php
$result = QueryBuilder::new()->exists('comments')->getQuery(); // ['has' => [['comments']]]
$result = QueryBuilder::new()->exists('comments', new SubQuery('where', ['likes', '>', 1000]))->getQuery(); // ['has' => [['column' => 'comments', 'method' => ['params' => ['likes', '>', 1000], 'method' => 'where' ]]]]
```

- sort

The `sort` clause allow developpers to apply a sort by `column` on the result set:

**Note** The sort order is defined by an integer value. Any value greater than `0` get converted to `ASC` while any value less than `0` is converted to `DESC`

```php
$result = QueryBuilder::new()->sort('created_at', -1)->getQuery(); // ['sort' => ['by' => 'created_at', 'order' => 'DESC']]
```

- select

The `select` clause allows developpers to specify the list of columns to select from the query server:

```php
$columns = QueryBuilder::new()->eq('title', 'Lorem Ipsum')->select('*', 'comments')->getColumns(); // ['*', 'comments'] 
```

## The REST query client

The REST query client is an implementation similar to the query language from `drewlabs/database` library that provides 4 overloaded methods `select()`, `update()`, `delete()` and `create()`, sort sending CRUD action to endpoint servers.

It required `drewlabs/curl-rest-client` library for actually doing the http request to server. Therefore in order to use the rest query client, developpers are required to execute the following command to install the required dependencies:

> composer require drewlabs/curl-rest-client
