<?php

namespace Oh86\Http\Response;

use Oh86\Http\Constants\ErrorCode;

class SimpleErrorResponse extends ErrorResponse
{
    public function __construct(int $code = ErrorCode::Error, int $status = 200, array $headers = [], $options = 0, bool $json = false)
    {
        parent::__construct($code, null, null, $status, $headers, $options, $json);
    }
}