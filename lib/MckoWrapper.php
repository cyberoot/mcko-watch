<?php

class MckoWrapper
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
    private $cabinetPath = '/new_mcko/index.php';

    private $authenticated = false;

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
        $response = $this->get($this->baseUrl . $this->cabinetPath, ['c' => 'dnevnik', 'd' => 'dnev']);

        return ResultsExtractor::FromMarkbook($response);
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

    public function Login($username, $password)
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

        $loginMatches  = null;
        $passwdMatches = null;

        // Detect login form fields
        preg_match("/name=\"(login\d+)\"/", $response, $loginMatches);
        preg_match("/name=\"(passwd\d+)\"/", $response, $passwdMatches);

        if (count($loginMatches) < 2 || count($passwdMatches) < 2)
        {
            throw new Exception('Cannot detect login form');
        }

        $fieldNames = ['login' => $loginMatches[1], 'password' => $passwdMatches[1]];

        $result = $this->post(
                    $this->baseUrl,
                    [
                        $fieldNames['login']    => $username,
                        $fieldNames['password'] => $password,
                    ],
                    false
        );

        return $this->authenticated = true;
    }

    private function isAuthenticated()
    {
        $response = $this->get($this->baseUrl, [], false);
        if (preg_match("/\sid=\"name_uch\"/", $response) != 0)
        { // already logged in
            return true;
        }

        return $response;
    }

    /**
     * @param string $url
     * @param array  $params
     *
     * @param bool   $chekAuth
     *
     * @return string
     */
    private function get($url, $params = [], $chekAuth = true)
    {
        $this->checkAuthorized($chekAuth);

        return $this->checkResponse($this->requestHandler->Get($url, $params));
    }

    /**
     * @param string $url
     * @param array  $params
     * @param bool   $chekAuth
     *
     * @return string
     */
    private function post($url, $params = [], $chekAuth = true)
    {
        $this->checkAuthorized($chekAuth);

        return $this->checkResponse($this->requestHandler->Post($url, $params));
    }

    public function Logout()
    {
        $this->get($this->baseUrl . $this->cabinetPath, ['submit_exit' => '1'], false);

        $this->authenticated = false;
    }
}
