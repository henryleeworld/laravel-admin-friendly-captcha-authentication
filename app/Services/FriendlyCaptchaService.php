<?php

namespace App\Services;

use GuzzleHttp\Client;

class FriendlyCaptchaService
{
    /**
     * FriendlyCaptcha secret
     *
     * @var string
     */
    protected $secret;

    /**
     * FriendlyCaptcha sitekey
     *
     * @var string
     */
    protected $sitekey;

    /**
     * FriendlyCaptcha verify endpoint
     */
    protected $verify;

    /**
     * error messages
     *
     * @var array
     */
    protected $error = [];

    public $isSuccess = false;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $http;

    public function __construct()
    {
        $this->secret   = config('friendlycaptcha.secret');
        $this->sitekey  = config('friendlycaptcha.sitekey');
        $this->verify   = config('friendlycaptcha.verify_endpoint');
        $this->http     = new Client(config('friendlycaptcha.options'));
    }

    /**
     * Verify FriendlyCaptcha response.
     *
     * @param string $solution
     *
     * @return bool
     */
    public function verifyRequest($solution)
    {
        return $this->verifyResponse(
            $solution,
        );
    }

    /**
     * Verify FriendlyCaptcha response.
     *
     * @param string $solution
     *
     * @return self
     */
    public function verifyResponse($solution)
    {
        if (empty($solution)) {
            return false;
        }

        $verifyResponse = $this->sendRequestVerify([
            'solution' => $solution,
            'secret'   => $this->secret,
            'sitekey'  => $this->sitekey,
        ]);

        if (isset($verifyResponse['success']) && $verifyResponse['success'] === true) {
            $this->isSuccess = true;
            return $this;
        }

        if (isset($verifyResponse['errors'])) {
            $this->errors  = $verifyResponse['errors'];
        }

        if (isset($verifyResponse['error'])) {
            $this->errors  = [$verifyResponse['error']];
        }

        $this->isSuccess = false;

        return $this;

    }

    /**
     * Send verify request.
     *
     * @param array $data
     *
     * @return array
     */
    protected function sendRequestVerify(array $data = [])
    {
        $response = $this->http->request('POST', $this->verify, [
            'form_params' => $data,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function isSuccess()
    {
        return $this->isSuccess;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
