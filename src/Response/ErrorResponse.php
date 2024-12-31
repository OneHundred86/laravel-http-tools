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
}