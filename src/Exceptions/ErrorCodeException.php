<?php

namespace Oh86\Http\Exceptions;

use Illuminate\Contracts\Support\Arrayable;
use Oh86\Http\Constants\ErrorCode;
use Oh86\Http\Response\ErrorResponse;
use Oh86\Http\Response\Result;

class ErrorCodeException extends \RuntimeException implements Arrayable
{
    protected int $errCode;
    protected string $errMessage;
    /** @var mixed */
    protected $data;

    protected int $status;
    protected array $headers;
    protected int $options;
    protected bool $json;

    /**
     * @param int $errCode
     * @param string|null $errMessage
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @param int $options
     * @param bool $json
     */
    public function __construct(
        int $errCode = ErrorCode::Error,
        ?string $errMessage = null,
        $data = null,
        int $status = 200,
        array $headers = [],
        $options = 0,
        bool $json = false
    ) {
        $this->errCode = $errCode;
        $this->errMessage = $errMessage ?? Result::codeToMessage($errCode);
        $this->data = $data;

        $this->status = $status;
        $this->headers = $headers;
        $this->options = $options;
        $this->json = $json;

        parent::__construct();
    }

    /**
     * @return int
     */
    public function getErrCode(): int
    {
        return $this->errCode;
    }

    /**
     * @return string
     */
    public function getErrMessage(): string
    {
        return $this->errMessage;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->errCode,
            'message' => $this->errMessage,
            'data' => $this->data,
        ];
    }

    /**
     * render response to client
     * @param \Illuminate\Http\Request  $request
     */
    public function render($request)
    {
        return new ErrorResponse(
            $this->errCode,
            $this->errMessage,
            $this->data,
            $this->status,
            $this->headers,
            $this->options,
            $this->json,
        );
    }
}
