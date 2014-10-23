<?php

class MrkoWrapper
{
    /**
     * @var null|RequestHandler
     */
    private $requestHandler = null;

    /**
     * @var string
     */
    private $baseUrl = '';

    /**
     * @var string
     */
    private $authenticated = false;

    private $loginPath = '/dnevnik/services/index.php';

    private $journalPath = '/dnevnik/services/dnevnik.php';

    private $mainPath = '/dnevnik/services/main.php';

    private $pguReferer = 'https://pgu.mos.ru/ru/application/dogm/journal/';

    function __construct($baseUrl, RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
        $this->baseUrl        = $baseUrl;
    }

    public function GetMarksForToday()
    {
        return $this->GetMarksForDayOfCurrentWeek();
    }

    public function GetMarksForDayOfCurrentWeek($date = null)
    {
        $todayMarks = [];
        if (empty($date))
        {
            $date = date("Y-m-d");
        }
        $stamp = strtotime($date);
        if (date('W', $stamp) != date('W'))
        {
            throw new Exception('I can only get marks for current week');
        }
        $weekMarks        = $this->GetMarkbook();
        $currentDayMarker = date("d.m", $stamp);

        $currentMarkerKey = array_filter(
            array_keys($weekMarks),
            function ($el) use ($currentDayMarker)
            {
                return preg_match("/{$currentDayMarker}\$/", $el) > 0;
            }
        );

        if (empty($currentMarkerKey))
        {
            return $todayMarks;
        }

        $currentMarkerKey = reset($currentMarkerKey);
        $todayMarks       = $weekMarks[$currentMarkerKey];

        return [ $currentMarkerKey => $todayMarks ];
    }

    public function GetMarkbook()
    {
        $response = $this->get($this->baseUrl . $this->journalPath, [ 'r' => 1 ]);

        return ResultsExtractor::FromMarkbook2014($response);
    }

    private function checkAuthorized($checkAuth = true)
    {
        if ($checkAuth && !$this->authenticated)
        {
            throw new Exception('Not authorized?');
        }
    }

    private function checkResponse($response)
    {
        if ($response === false)
        {
            throw new Exception($this->requestHandler->LastError());
        }

        return $response;
    }

    public function Login($username, $password, $token)
    {
        if ($this->authenticated)
        {
            return true;
        }

        $response = $this->isAuthenticated();

        if ($response === true)
        {
            return $this->authenticated = true;
        }

        $result = $this->post(
            $this->baseUrl . $this->loginPath,
            [
                'login'    => $username,
                'pass'     => $password,
                'password' => $token,
            ],
            false,
            $this->pguReferer
        );

        $lastCode = $this->requestHandler->LastHttpCode();

        $response = $this->isAuthenticated();

        if ($response === true)
        {
            return $this->authenticated = true;
        }

        return $this->authenticated = false;
    }

    private function isAuthenticated()
    {
        $this->get($this->baseUrl . $this->journalPath, [], false);

        if ($this->requestHandler->LastHttpCode() == 200)
        { // already logged in
            return true;
        }

        return false;
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @param bool   $chekAuth
     *
     * @return string
     */
    private function get($url, $params = [], $chekAuth = true, $referer = null)
    {
        $this->checkAuthorized($chekAuth);

        return $this->checkResponse($this->requestHandler->Get($url, $params, $referer));
    }

    /**
     * @param string $url
     * @param array  $params
     * @param bool   $chekAuth
     *
     * @return string
     */
    private function post($url, $params = [], $chekAuth = true, $referer = null)
    {
        $this->checkAuthorized($chekAuth);

        return $this->checkResponse($this->requestHandler->Post($url, $params, $referer));
    }

    public function Logout()
    {
        $this->get($this->baseUrl . $this->mainPath, ['exit' => '1'], false, $this->baseUrl . $this->journalPath );

        $this->authenticated = false;
    }
}
