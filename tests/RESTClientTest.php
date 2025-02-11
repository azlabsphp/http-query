<?php

namespace Drewlabs\Query\Http\Tests;

use Drewlabs\Query\Http\Query;
use Drewlabs\Query\Http\Response;
use Drewlabs\Query\Http\TestClient;
use PHPUnit\Framework\TestCase;

class RESTClientTest extends TestCase
{
    /**
     * Returns a list of posts
     * 
     * @return (int|string)[][] 
     */
    private function getPosts()
    {
        return [
            [
                'id' => 1,
                'title' => 'Lorem Picsum',
                'content' => "De nombreuses suites logicielles de mise en page ou éditeurs de sites Web ont fait du Lorem Ipsum leur faux texte par défaut, et une recherche pour 'Lorem Ipsum' vous conduira vers de nombreux sites qui n'en sont encore qu'à leur phase de construction"
            ],
            [
                'id' => 2,
                'title' => 'Environments',
                'content' => "On sait depuis longtemps que travailler avec du texte lisible et contenant du sens est source de distractions, et empêche de se concentrer sur la mise en page elle-même"
            ]
        ];
    }

    /**
     * Returns the post matching the $id parameter
     * 
     * @param string|int $id 
     * @return mixed 
     */
    private function getPost($id)
    {
        return array_values(array_filter($this->getPosts(), function ($post) use ($id) {
            return isset($post['id']) && ($post['id'] === $id);
        }))[0] ?? null;
    }

    /**
     * Creates an in memory post value
     * 
     * @return (int|string)[] 
     */
    private function createPost()
    {
        return [
            'id' => 3,
            'title' => 'Software Engineering',
            'content' => "On sait depuis longtemps"
        ];
    }

    public function updatePost()
    {
        return [
            'id' => 2,
            'title' => 'Environments',
            'content' => "On sait depuis longtemps, Text updated!"
        ];
    }

    public function test_create_query()
    {
        TestClient::for('api/posts', new Response(json_encode(['data' => $this->createPost()]), 200), 'POST');
        $result = Query::new('http://localhost/api/posts')->test()->create([]);
        $this->assertEquals($this->createPost(), $result->get('data'));
    }

    public function test_get_query()
    {
        TestClient::for('api/posts', new Response(json_encode(['data' => $this->getPosts()]), 200));
        TestClient::for('api/posts/2', new Response(json_encode($this->getPost(2)), 200));
        $result = Query::new('http://localhost/api/posts')->test()->get(2);
        $this->assertEquals($this->getPost(2), $result->getBody());
        $result = Query::new('http://localhost/api/posts')->test()->get();
        $this->assertEquals($this->getPosts(), $result->get('data'));
    }

    public function test_update_query()
    {
        TestClient::for('api/posts/2', new Response(json_encode($this->updatePost()), 200), 'PUT');
        $result = Query::new('http://localhost/api/posts')->test()->update(2, []);
        $this->assertEquals($this->updatePost(), $result->getBody());
    }

    public function test_delete_query()
    {
        TestClient::for('api/posts/2', new Response(json_encode(false), 200), 'DELETE');
        $result = Query::new('http://localhost/api/posts')->test()->delete(2);
        $this->assertEquals(200, $result->getStatusCode());
    }
}
