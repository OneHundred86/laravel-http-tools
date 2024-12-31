<?php

namespace Oh86\Http\TokenSessions;

use Illuminate\Support\Arr;
use Serializable;
use Stringable;

class SessionData implements Serializable, Stringable
{
    private array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    public function put(string $key, $value)
    {
        Arr::set($this->data, $key, $value);
    }

    public function pull(string $key, $default = null)
    {
        return Arr::pull($this->data, $key, $default);
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     * 
     * @param string|string[] $keys
     * @return void
     */
    public function forget($keys)
    {
        Arr::forget($this->data, $keys);
    }

    public function clear()
    {
        $this->data = [];
    }

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    public function __toString(): string
    {
        return $this->serialize();
    }
}