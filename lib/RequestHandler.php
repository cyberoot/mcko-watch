<?php

class RequestHandler
{

    /**
     * @var null|resource
     */
    private $curl = null;

    /**
     * @var string
     */
    private $cookies = null;

    private $lastUrl = '';

    /**
     * @var array
     */
    private $errors = [];

    function __construct($cookies)
    {
        $this->cookies = $cookies;
    }

    function __destruct()
    {
        $this->finishRequest();
    }

    private static function getHeaders()
    {
        return
            [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*;q=0.8',
                'Accept-Language: ru,en-us;q=0.7,en;q=0.3',
                'Accept-Encoding: deflate',
                'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7'
            ];
    }

    private static function getAgent()
    {
        return "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36";
    }

    private function finishRequest()
    {
        if ($this->curl != null)
        {
            @curl_close($this->curl);
        }
    }

    private function initRequest()
    {
        $this->curl = curl_init();
        curl_setopt_array($this->curl,
                          [
                              CURLOPT_HTTPHEADER => self::getHeaders(),
                              CURLOPT_FAILONERROR => true,
                              CURLOPT_FOLLOWLOCATION => true,
                              CURLOPT_RETURNTRANSFER => true,
                              CURLOPT_TIMEOUT => 20,
                              CURLOPT_USERAGENT => self::getAgent(),
                              CURLOPT_COOKIEJAR => $this->cookies,
                              CURLOPT_COOKIEFILE => $this->cookies,
                              CURLOPT_SSL_VERIFYPEER => false,
                              CURLOPT_SSL_VERIFYHOST => false,
                          ]
        );
        if(!empty($this->lastUrl))
        {
            curl_setopt($this->curl, CURLOPT_REFERER, $this->lastUrl);
        }
    }

    public function Get($url, $params = [])
    {
        return $this->request($url, $params, 'GET');
    }

    public function Post($url, $params)
    {
        return $this->request($url, $params, 'POST');
    }

    private function request($url, $params, $method)
    {
        $this->initRequest();
        $paramsQuery = http_build_query($params);

        $this->lastUrl = $url;

        switch($method)
        {
            case 'GET':
                curl_setopt($this->curl, CURLOPT_URL, $url . ( !empty($params) ? '?' . $paramsQuery : '' ) );
                break;
            case 'POST':
                curl_setopt($this->curl, CURLOPT_URL, $url);
                curl_setopt($this->curl, CURLOPT_POST, true);
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $paramsQuery);
                break;
            default:
                throw new Exception('Not implemented');
                break;
        }

        $response = curl_exec($this->curl);

        if (curl_errno($this->curl))
        {
            $this->errors[] =  curl_error($this->curl);
        }

        $this->finishRequest();

        return $response;
    }

    public function LastError()
    {
        return array_pop($this->errors);
    }
}
