<?php

use Drewlabs\RestQuery\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{

    public function test_where_clause()
    {
        $builder = QueryBuilder::new()
            ->eq('title', 'Lorem Ipsum')
            ->neq('id', 10)
            ->where(function (QueryBuilder $builder) {
                return $builder->in('tags', ['I', 'L', 'F'])
                    ->gt('likes', 120)
                    ->lte('rates', 4.5)
                    ->lt('comments', 1200)
                    ->gte('groups', 10);
            });

        $result = $builder->getQuery()['where'] ?? [];
        $this->assertEquals(['title', '=', 'Lorem Ipsum'], $result[0]);
        $this->assertEquals(['id', '<>', 10], $result[1]);
        $this->assertEquals('query', $result[2]['method']);
        $this->assertEquals(['tags', ['I', 'L', 'F']], $result[2]['params']['in'][0]);
    }

    public function test_or_clause()
    {
        $builder = QueryBuilder::new()
            ->where('title', 'Lorem Ipsum')
            ->or('id', 10);

        $result = $builder->getQuery()['where'] ?? [];
        $orResult = $builder->getQuery()['orwhere'] ?? [];
        $this->assertEquals(['title', '=', 'Lorem Ipsum'], $result[0]);
        $this->assertEquals(['id', '=', 10], $orResult[0]);
    }

    public function test_not_clause()
    {
        $builder = QueryBuilder::new()->or('id', 10)->neq('likes', 4);
        $result = $builder->getQuery()['where'] ?? [];
        $orResult = $builder->getQuery()['orwhere'] ?? [];
        $this->assertEquals(['likes', '<>', 4], $result[0]);
        $this->assertEquals(['id', '=', 10], $orResult[0]);
    }

    public function test_in_clause()
    {
        $builder = QueryBuilder::new()->in('likes', [5, 10])->in('id', [90, 120, 167]);
        $result = $builder->getQuery()['in'] ?? [];
        $this->assertEquals(['likes', [5, 10]], $result[0]);
        $this->assertEquals(['id', [90, 120, 167]], $result[1]);
    }

    public function test_exists_clause()
    {
        $builder = QueryBuilder::new()->exists('comments', function(QueryBuilder $query) {
            return $query->gte('likes', 100);
        });
        $query = $builder->getQuery();
        $this->assertTrue(array_key_exists('has', $query));
        $this->assertEquals(['likes', '>=', 100], $query['has'][0]['match']['params']['where'][0]);
    }

    public function test_sort_clause()
    {
        $builder = QueryBuilder::new()->sort('created_at', -1);
        $result = $builder->getQuery()['sort'] ?? [];
        $this->assertEquals(['order' => 'DESC', 'by' => 'created_at'], $result);
    }
}
