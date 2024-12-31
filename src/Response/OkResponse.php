<?php

namespace Oh86\Http\Response;

use Oh86\Http\Constants\ErrorCode;

class OkResponse extends ErrorResponse
{
    public function __construct($data = null, int $status = 200, array $headers = [], $options = 0, bool $json = false)
    {
        parent::__construct(ErrorCode::OK, null, $data);
    }
}