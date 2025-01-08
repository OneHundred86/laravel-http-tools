<?php

namespace Oh86\Http\TokenSessions;

use Illuminate\Support\Str;

abstract class AbstractTokenSession
{
    /**
     * @var string 
     */
    protected $storeKey = '';

    /**
     * @var int
     */
    protected $ttl = 300;

    /**
     * 更新ttl策略：once（只在首次保存的时候设置）/alwaysOnSave（每次保存都更新）
     * @var string
     */
    protected $ttlUpdateStrategy = 'alwaysOnSave';


    private string $token;
    private bool $isDirty = false;
    private SessionData $data;
    private bool $everBeenStored = false; // 是否曾经存储过


    /** 
     * @var \Illuminate\Redis\Connections\PhpRedisConnection $store
     * @see \Redis 
     */
    protected $store;

    /**
     * @param null|string $token     null | '' 表示自动生成token
     */
    public function __construct(?string $token = null)
    {
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
        $ins = new static($token);
        $ins->loadData();
        $ins->everBeenStored = true;

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

    public function getStoreKey(): string
    {
        return $this->storeKey ?: get_class($this);
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

    public function flush(): void
    {
        $this->data->flush();
        $this->isDirty = true;
    }

    public function increment(string $key, int $value = 1): int
    {
        $ret = $this->data->increment($key, $value);
        $this->isDirty = true;
        return $ret;
    }

    public function decrement(string $key, int $value = 1): int
    {
        $ret = $this->data->decrement($key, $value);
        $this->isDirty = true;
        return $ret;
    }

    public function save(bool $force = false): bool
    {
        $ret = false;
        $key = $this->getRealStoreKey();
        if ($force || $this->isDirty) {
            if ($this->ttlUpdateStrategy == 'alwaysOnSave') {
                $ret = $this->store->set($key, $this->data->serialize(), 'EX', $this->ttl);
            } elseif ($this->ttlUpdateStrategy == 'once') {
                if ($this->everBeenStored) { // 不修改ttl
                    $oldTTL = $this->store->ttl($key);
                    if ($oldTTL > 0) {
                        $ret = $this->store->set($key, $this->data->serialize(), 'EX', $oldTTL, 'XX');
                    } elseif ($oldTTL === -1) { // 永久保存
                        $ret = $this->store->set($key, $this->data->serialize());
                    } else { // -2原先不存在，已过期，不保存
                        $ret = false;
                    }
                } else { // 首次保存
                    $ret = $this->store->set($key, $this->data->serialize(), 'EX', $this->ttl);
                }
            }

            $this->everBeenStored = true;
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
        $this->store->del($this->getRealStoreKey());
        $this->isDirty = false;
    }

    public function getRealStoreKey(): string
    {
        return $this->wrapToken($this->token);
    }

    protected function wrapToken(string $token): string
    {
        return sprintf("%s:%s", $this->getStoreKey(), $token);
    }

    protected function loadData()
    {
        $serialized = $this->store->get($this->getRealStoreKey());
        if ($serialized) {
            $this->data->unserialize($serialized);
        }
    }
}