<?php

namespace Oh86\Http\Response;

use Oh86\Http\Constants\ErrorCode;

class ErrorMessageResponse extends ErrorResponse
{
    public function __construct(string $message, int $status = 200, array $headers = [], $options = 0, bool $json = false)
    {
        parent::__construct(ErrorCode::Error, $message, null, $status, $headers, $options, $json);
    }
}