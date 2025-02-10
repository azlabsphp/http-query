<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http;

use Drewlabs\Curl\Client as Curl;
use Drewlabs\Query\Http\Exceptions\RequestException;

final class Client
{
    /** @var Curl */
    private $curl;

    /** @var bool */
    private $testing;

    /**
     * client class construct
     * 
     * @return void 
     */
    private function __construct(bool $testing = false)
    {
        $this->curl = new Curl();
        $this->testing = $testing;
    }

    /**
     * Class factory constructor
     * 
     * @return static 
     */
    public static function new(bool $testing = false)
    {
        return new static($testing);
    }


    /**
     * Send request to the backend server and return the result to the caller
     * 
     * @param string $url 
     * @param string $method 
     * @param array $body 
     * @param array $headers 
     * @return Response 
     * @throws RuntimeException 
     * @throws RequestException 
     */
    public function sendRequest(string $url, string $method = 'GET', array $body = [], array $headers = []): Response
    {
        //# TODO: Case testing, do someting

        // Reset the current curl instance before sending any new HTTP request
        $this->curl->release();
        $this->curl->init();
        $this->curl->setOption(\CURLOPT_RETURNTRANSFER, true);

        // Sends the request to the coris webservice host
        $this->curl->send([
            'method' => $method,
            'url' => $url,
            'headers' => array_merge($headers ?? [
                'Content-Type' => 'application/json',
                'Accept' => '*'
            ]),
            'body' => $body
        ]);
        $statusCode = $this->curl->getStatusCode();
        if ((200 > $statusCode || 204 < $statusCode)) {
            throw new RequestException(sprintf('/%s %s, [%s] %s', $method, $url, $statusCode, $this->curl->getResponse() ?? ''));
        }

        return new Response($this->curl->getResponse() ?? '', intval($statusCode), $this->parseHeaders($this->curl->getResponseHeaders() ?? ''));
    }

    /**
     * Parse request string headers.
     *
     * @param ?string $headers
     *
     * @return array
     */
    private function parseHeaders(string $headers)
    {
        $headers = preg_split('/\r\n/', (string) ($headers ?? ''), -1, \PREG_SPLIT_NO_EMPTY);
        $httpHeaders = [];
        $httpHeaders['Request-Line'] = reset($headers) ?? '';
        for ($i = 1; $i < \count($headers); ++$i) {
            if (str_contains($headers[$i], ':')) {
                [$key, $value] = array_map(static function ($item) {
                    return $item ? trim($item) : null;
                }, explode(':', $headers[$i], 2));
                $httpHeaders[$key] = $value;
            }
        }

        return $httpHeaders;
    }

    public function __destruct()
    {
        $this->curl->close();
    }
}
