<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http;

use Drewlabs\Query\Http\Contracts\ClientInterface;
use Drewlabs\Query\Http\Exceptions\RequestException;

final class TestClient implements ClientInterface
{
    /** @var array<string,Response> */
    private static $handlers = [];

    /**
     * Set route maps for test client
     * 
     * @param array<string,string|ResponseInterface|array<string|ResponseInterface>> $map 
     * @return void 
     */
    public static function setRouteResponses(array $map)
    {
        foreach ($map as $key => $value) {
            $key = substr(strval($key), 0, 1) === '/' ? strval($key) : "/$key";
            self::$handlers[$key][] = is_string($value) ? [$value, new Response('', 200, [])] : ($value instanceof Response ? ['GET', $value] : $value);
        }
    }

    /**
     * Bind a response for a given route definition
     * 
     * @param string $name 
     * @param ResponseInterface $response 
     * @param string $method 
     * @return void 
     */
    public static function for(string $name, Response $response, string $method = 'GET')
    {
        $name = substr(strval($name), 0, 1) === '/' ? strval($name) : "/$name";
        self::$handlers[$name][] = [$method ?? 'GET', $response];
    }

    public function sendRequest(string $url, string $method = 'GET', array $body = [], array $headers = []): Response
    {
        return $this->findMatch($url, $method);
    }

    /**
     * Find the response matching the request url
     * 
     * @param mixed $url 
     * @return mixed 
     * @throws BadRequestException 
     */
    private function findMatch($url, string $method)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (null === $host) {
            throw new RequestException("Unable to resolve host URL");
        }
        $rest = substr($url, strpos($url, $host) + strlen($host));

        if (!array_key_exists($rest, self::$handlers)) {
            throw new RequestException('Bad request', 404);
        }
        $definitions = self::$handlers[$rest];
        if (!is_array($definitions)) {
            throw new RequestException("Invalid response definition for $url");
        }
        $response = array_values(array_filter($definitions, function ($value) use ($method) {
            return is_array($value) && strtoupper($value[0]) === strtoupper($method);
        }))[0] ?? null;

        if (!is_array($response) || !isset($response[1])) {
            throw new RequestException('Bad request', 403);
        }
        return $response[1];
    }
}
