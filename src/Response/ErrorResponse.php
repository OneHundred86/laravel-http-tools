<?php

namespace Oh86\Http\Response;

use Illuminate\Http\JsonResponse;
use Oh86\Http\Constants\ErrorCode;

class ErrorResponse extends JsonResponse
{
    public function __construct(
        int $code = ErrorCode::Error,
        ?string $message = null,
        $data = null,
        int $status = 200,
        array $headers = [],
        $options = 0,
        bool $json = false
    ) {
        $result = Result::error($code, $message, $data);
        parent::__construct($result, $status, $headers, $options, $json);
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        /** @var Result $result */
        $result = $this->original;
        $result->setCode($code);

        return $this;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        /** @var Result $result */
        $result = $this->original;
        $result->setMessage($message);

        return $this;
    }

    /**
     * 局部修改data
     * @param string $key
     * @param mixed $value
     */
    public function dataSet($key, $value)
    {
        /** @var Result $result */
        $result = $this->original;
        $result->dataSet($key, $value);

        return $this;
    }

    /**
     * @override
     *
     * @return $this
     */
    public function sendContent()
    {
        $this->setData($this->original);

        return parent::sendContent();
    }
}