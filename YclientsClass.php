<?php

class Yclients
{
    const URL = 'https://api.yclients.com/api/v1/';

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';

    private $tokenPartner;
    private $userToken;

    function __construct($tokenPartner, $password, $login)
    {
        $this->setTokenPartner($tokenPartner);
        $this->setUserToken($password, $login);
    }

    public function setTokenPartner($tokenPartner)
    {
        $this->tokenPartner = $tokenPartner;
        return $this;
    }
    public function setUserToken($password, $login)
    {
        $this->userToken = $this->auth($password, $login)['data']['user_token'];
        return $this;
    }

    public function getTokenPartner()
    {
        return $this->tokenPartner;
    }

    public function getUserToken()
    {
        return $this->userToken;
    }

    public function auth($password, $login)
    {
        return $this->request(
            self::URL . 'auth',
            self::POST,
            [
                'login' => $login,
                'password' => $password
            ]);
    }

    public function request($url, $method, $params = [], $userToken = false)
    {
        if($this->userToken)
        {
            $userToken = $this->userToken;
        }
        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->tokenPartner . ', User ' . $userToken,
            'Accept: application/vnd.yclients.v2+json',
            'Content-type: application/json'
        ];


        if($method === self::GET)
        {
            $url .=  '?' . http_build_query($params);
        }
        elseif($method === self::POST)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        elseif($method === self::PUT)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::PUT);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        $response = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];

        if ($code < 200 || $code > 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
        }
        curl_close($ch);

        return json_decode($response, true);
    }
}
