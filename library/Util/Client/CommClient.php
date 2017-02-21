<?php
/**
 * Client.php
 * User: lanse
 */
namespace ROOT\Library\Util\Client;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise;

class CommClient
{
    private $appkey;
    private $appsecret;
    //guzzle client
    private $client;
    //签名有效期
    const TTL = 100;

    //签名数据
    private $contents = [];
    //是否带有文件
    private $with_file = false;

    //logger对象
    private $oLogger;

    /**
     * CommClient constructor.
     * @param $appkey
     * @param $appsecret
     * @param array $config
     */
    public function __construct($appkey, $appsecret, $config = [])
    {
        $this->appkey = $appkey;
        $this->appsecret = $appsecret;

        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            $queryParams = $this->getSignInfo($request);
            $oUri = $request->getUri()->withQuery(http_build_query($queryParams));

            return $request->withUri($oUri);
        }));

        $config['handler'] = $stack;
        $this->client = new Client($config);
    }

    /**
     * @param $uri
     * @param array $params
     * @return bool
     */
    public function get($uri, $params = [], $options = [])
    {
        return $this->request('GET', $uri, $params, $options);
    }

    /**
     * @param $uri
     * @param $params
     * @return bool
     */
    public function post($uri, $params = [], $options = [])
    {
        return $this->request('POST', $uri, $params, $options);
    }

    /**
     * @param $uri
     * @param array $query
     * @param string $body array/string
     * @param array $options guzzle options
     * @param string $contentType json/form_params
     * @return bool
     */
    public function delete($uri, $query = [], $body = [], $options = [], $contentType = 'json')
    {
        if (!empty($query)) {
            $options['query'] = $query;
        }
        $options[$contentType] = $this->parseBody($body, $contentType);
        return $this->request('DELETE', $uri, [], $options);
    }

    /**
     * @param $uri
     * @param array $query
     * @param string $body array/string
     * @param array $options guzzle options
     * @param string $contentType json/form_params
     * @return bool
     */
    public function put($uri, $query = [], $body = [], $options = [], $contentType = 'json')
    {
        if (!empty($query)) {
            $options['query'] = $query;
        }
        $options[$contentType] = $this->parseBody($body, $contentType);
        return $this->request('PUT', $uri, [], $options);
    }

    /**
     * @param $uri
     * @param $params
     * @param $files
     * @return bool
     */
    public function postWithFiles($uri, $params = [], $files = [], $options = [])
    {
        $this->contents = $params;
        $this->with_file = true;

        $options['multipart'] = [];

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $options['multipart'][] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
        }

        if (!empty($files)) {
            foreach ($files as $key => $file) {
                if (!file_exists($file)) {
                    continue;
                }
                $options['multipart'][] = [
                    'name' => $key,
                    'contents' => @fopen($file, 'r'),
                ];
            }
        }

        try {
            $response = $this->client->request('POST', $uri, $options);
            $body = $response->getBody();

            $this->contents = [];
            $this->with_file = false;
            return json_decode($body, true);
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $url = $this->client->getConfig('base_uri') . $uri;
            $this->logError('commapi 请求错误，' . $url . ',参数：' . json_encode($params) . ',错误:' . $e->getMessage());
        }
        $this->contents = [];
        $this->with_file = false;
        return false;
    }

    /**
     * @param $method
     * @param $uri
     * @param $params
     * @return bool
     */
    public function request($method, $uri, $params = [], $options = [])
    {
        switch ($method) {
            case 'GET':
                $options['query'] = $params;
                break;
            case 'POST':
                $options['form_params'] = $params;
                break;
            default:
                if (!empty($params)) {
                    $options['body'] = $params;
                }
                break;
        }

        try {
            $response = $this->client->request(strtoupper($method), $uri, $options);
            $body = $response->getBody();
            return json_decode($body, true);
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $url = $this->client->getConfig('base_uri') . $uri;
            $this->logError('commapi 请求错误，' . $url . ',参数：' . json_encode($params) . ',错误:' . $e->getMessage());
        }
        return false;
    }

    /**
     * @param array $requests
     * @return array
     */
    public function batchRequest($requests = [])
    {
        $promises = [];

        foreach ($requests as $k => $request) {
            switch (strtoupper($request['method'])) {
                case 'GET':
                    $promises[$k] = $this->client->getAsync($request['url'], ['query' => $request['params']]);
                    break;
                case 'POST':
                    $promises[$k] = $this->client->postAsync($request['url'], ['form_params' => $request['params']]);
                    break;
                default:
                    break;
            }
        }

        $results = Promise\settle($promises)->wait();

        $data = [];

        foreach ($results as $k => $v) {
            $data[$k] = (array)@json_decode($v['value']->getBody()->getContents(), true);
        }

        return $data;
    }

    public function getClient()
    {
        return $this->client;
    }

    //设置记录日志的对象
    public function setLogger($loggerObj)
    {
        if (is_object($loggerObj) && method_exists($loggerObj, 'logError')) {
            $this->oLogger = $loggerObj;
        }
        return $this;
    }

    private function logError($msg)
    {
        $this->getLogger()->logInfo($msg);
    }

    private function getLogger()
    {
        if (!$this->oLogger) {
            $this->oLogger = new \Pub\Log\Logger('/data/service_logs/services/', 'commapi_request');
        }
        return $this->oLogger;
    }

    private function getQueryParams(RequestInterface $request)
    {
        $query = $request->getUri()->getQuery();
        $params = [];

        if (!empty($query)) {
            parse_str($query, $params);
        }

        return $params;
    }

    private function getBodyParams(RequestInterface $request)
    {
        $body = $request->getBody()->getContents();

        if (!empty($body)) {
            $contentType = $request->getHeader('Content-Type');
            return $this->parseBody($body, current($contentType));
        }

        return [];
    }

    /**
     * @param $body array/string
     * @param string $contentType json/form
     */
    private function parseBody($body, $contentType = 'json')
    {
        if (is_string($body)) {
            if (!empty($body)) {
                if (false !== strpos($contentType, 'json')) {
                    $body = json_decode($body, true);
                } else {
                    parse_str($body, $body);
                }
            } else {
                return $body = [];
            }
        }
        return $body;
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    private function getSignInfo(RequestInterface $request)
    {
        $queryParams = $this->getQueryParams($request);
        if ($this->with_file) {
            $bodyParams = $this->contents;
        } else {
            $bodyParams = $this->getBodyParams($request);
        }

        $params = array_merge($queryParams, $bodyParams);

        $extParams = [];
        $extParams['appkey'] = $this->appkey;
        $extParams['nonce'] = uniqid();
        $extParams['expires'] = self::TTL + time();

        $params = array_merge($params, $extParams);
        //按照key进行排序
        ksort($params);

        /*
        $paramsToSign = [];

        foreach ($params as $key => $val) {
            $paramsToSign[] = $key . '=' . urlencode($val);
        }
        $paramsToSign = implode('&', $paramsToSign);
        */
        $paramsToSign = http_build_query($params);

        $extParams['signature'] = substr(md5(base64_encode(hash_hmac('sha256', $paramsToSign, $this->appsecret, true))), 5, 10);

        return array_merge($queryParams, $extParams);
    }
}
