<?php

namespace Oh86\Http\Exceptions;

use Illuminate\Contracts\Support\Arrayable;
use Oh86\Http\Constants\ErrorCode;
use Oh86\Http\Response\ErrorResponse;

class HttpRequestException extends \Exception implements Arrayable
{
    /**
     * 响应状态码
     * @var int
     */
    protected $status;

    /**
     * 响应体
     * @var string
     */
    protected $response;

    /**
     * 请求路径
     * @var string
     */
    protected $url;

    /**
     * 请求参数
     * @var array
     */
    protected $datas;

    /**
     * 请求头
     * @var array
     */
    protected $headers;


    /**
     * @param int $status   响应状态码
     * @param string $response     响应体
     * @param string $url      请求url
     * @param array $datas   请求参数
     * @param array $headers  请求头
     */
    public function __construct($status, $response, $url, $datas = null, $headers = null)
    {
        $this->status = $status;
        $this->response = $response;
        $this->url = $url;
        $this->datas = $datas;
        $this->headers = $headers;
        parent::__construct("请求错误：" . $response, $status);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array|null
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array|null
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "status" => $this->status,
            "response" => $this->response,
            "url" => $this->url,
            "datas" => $this->datas,
            "headers" => $this->headers,
        ];
    }

    /**
     * render response to client
     * @param \Illuminate\Http\Request $request
     */
    public function render($request)
    {
        return new ErrorResponse(ErrorCode::Error, '请求错误', $this->toArray(), 500);
    }
}
