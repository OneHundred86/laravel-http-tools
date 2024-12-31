<?php

namespace Oh86\Http\TokenSessions;

use Illuminate\Support\Str;

abstract class AbstractTokenSession
{
    protected string $token;
    /**
     * @var null|string 
     */
    protected $storeKeyPrefix = null;
    private bool $isDirty = false;
    private SessionData $data;

    /**
     * >1 表示设置ttl；
     * 0 表示不修改ttl；
     * -1 表示永久有效；
     */
    protected int $ttl;

    /** 
     * @var \Illuminate\Redis\Connections\PhpRedisConnection $store
     * @see \Redis 
     */
    protected $store;

    /**
     * @param int $ttl  >1 表示设置ttl；0 表示不修改ttl；-1 表示永久有效
     * @param string $token     ''表示自动生成token
     */
    final public function __construct(int $ttl = 60 * 10, string $token = '')
    {
        $this->ttl = $ttl;

        $this->token = $token ?: Str::random(20);

        $this->data = new SessionData();
        $this->store = app('redis');
    }

    /**
     * @param string $token
     * @return static
     */
    public static function load(string $token)
    {
        // 要求：不允许重写构造函数
        $ins = new static(0, $token);
        $ins->loadData();

        return $ins;
    }

    public function __destruct()
    {
        $this->save();
    }

    public function getStore()
    {
        return $this->store;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function getStoreKeyPrefix(): string
    {
        return $this->storeKeyPrefix ?: get_class($this);
    }

    public function has(string $key): bool
    {
        return $this->data->has($key);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data->get($key, $default);
    }

    public function all(): array
    {
        return $this->data->all();
    }

    public function pull(string $key, $default = null)
    {
        $ret = $this->data->pull($key, $default);
        $this->isDirty = true;
        return $ret;
    }

    public function put(string $key, $value): void
    {
        $this->data->put($key, $value);
        $this->isDirty = true;
    }

    /**
     * @param string|string[] $keys
     */
    public function forget($keys): void
    {
        $this->data->forget($$keys);
        $this->isDirty = true;
    }

    public function save(bool $force = false): bool
    {
        $ret = false;
        $key = $this->getStoreKey();
        if ($force || $this->isDirty) {
            if ($this->ttl > 0) { // 设置ttl
                $ret = $this->store->set($key, $this->data->serialize(), 'EX', $this->ttl);
            } elseif ($this->ttl === 0) {   // 不修改ttl
                $oldTTL = $this->store->ttl($key);
                if ($oldTTL > 0) {
                    $ret = $this->store->set($key, $this->data->serialize(), 'EX', $oldTTL, 'XX');
                } elseif ($oldTTL === -1) { // 永久保存
                    $ret = $this->store->set($key, $this->data->serialize());
                } else { // -2原先不存在，已过期，不保存
                    $ret = false;
                }
            } else { // 永久保存
                $ret = $this->store->set($key, $this->data->serialize());
            }
        }

        $this->isDirty = false;

        return $ret;
    }

    /**
     * 销毁token会话
     * @return void
     */
    public function destroy(): void
    {
        $this->data->clear();
        $this->store->del($this->getStoreKey());
        $this->isDirty = false;
    }

    public function getStoreKey(): string
    {
        return $this->wrapToken($this->token);
    }

    protected function wrapToken(string $token): string
    {
        return sprintf("%s:%s", $this->getStoreKeyPrefix(), $token);
    }

    protected function loadData()
    {
        $serialized = $this->store->get($this->getStoreKey());
        if ($serialized) {
            $this->data->unserialize($serialized);
        }
    }
}