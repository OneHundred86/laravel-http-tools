<?php

namespace Oh86\Http\Response;

use Oh86\Http\Constants\ErrorCode;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;


class Result implements Arrayable
{
    protected int $code;
    protected string $message;
    protected $data;

    /**
     * @param int $code
     * @param string $message
     * @param mixed $data
     */
    public function __construct(int $code, string $message, $data)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function dataSet($key, $value)
    {
        $this->data ??= [];
        Arr::set($this->data, $key, $value);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }

    public static function ok($data)
    {
        return new Result(
            ErrorCode::OK,
            self::codeToMessage(ErrorCode::OK),
            $data
        );
    }

    public static function error(int $code, ?string $message = null, $data = null)
    {
        return new Result(
            $code,
            $message ?? self::codeToMessage($code),
            $data
        );
    }

    /**
     * code to message
     * @param int $code
     * @return string
     */
    public static function codeToMessage(int $code): string
    {
        return Arr::get(config('errorcode.messages'), $code, '');
    }
}
