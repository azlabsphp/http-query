<?php

declare(strict_types=1);

namespace Drewlabs\Query\Http;

use ArrayAccess;
use Drewlabs\Query\Http\Concerns\Response;
use RuntimeException;

final class QueryResult implements ArrayAccess
{
    use Response;

    /** @var array<string,mixed> */
    private $data;

    /**
     * Creates new json response class instance
     * 
     * @param array $data 
     * @param int $status 
     * @param array $headers 
     * @return void 
     */
    public function __construct(array $data = [], int $status = 200, array $headers = [])
    {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers ?? [];
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data) && isset($this->data[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Cannot modify response body');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Cannot modify response body');
    }

    /**
     * returns the json response body
     * 
     * @return array 
     */
    public function getBody(): array
    {
        return $this->data;
    }

    /**
     * returns true if `status` property is greater 199 and less than 205
     * 
     * @return bool 
     */
    public function isOk()
    {
        return $this->status >= 200 && 204 >= $this->status;
    }

    /**
     * Get the value of a given key or default if no value is found
     * 
     * @param string $name 
     * @param mixed $default 
     * @return mixed 
     */
    public function get(string $name, $default = null)
    {
        $default = (!is_string($default) && is_callable($default) ? $default : function () use ($default) {
            return $default;
        });

        if (false !== strpos($name, '.')) {
            $keys = explode('.', $name);
            $current = $this->data;
            foreach ($keys as $key) {
                if (is_null($current)) {
                    return call_user_func($default, $name);
                }
                $current = array_key_exists($key, $current) ? $current[$key] : $current[$key] ?? null;
            }
            return $current ?? call_user_func($default, $name);
        }
        return $this->data[$name] ?? call_user_func($default, $name);
    }
}
