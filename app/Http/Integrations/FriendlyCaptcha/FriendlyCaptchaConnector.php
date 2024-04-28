<?php

namespace App\Http\Integrations\FriendlyCaptcha;

use GuzzleHttp\Client;

class FriendlyCaptchaConnector
{
    /**
     * FriendlyCaptcha secret
     *
     * @var string
     */
    protected string $secret;

    /**
     * FriendlyCaptcha sitekey
     *
     * @var string
     */
    protected string $sitekey;

    /**
     * FriendlyCaptcha verify endpoint
     */
    protected string $verify;

    /**
     * error messages
     *
     * @var array
     */
    protected array $error = [];

    public bool $isSuccess = false;

    /**
     * @var \GuzzleHttp\Client
     */
    protected Client $http;

    public function __construct()
    {
        $this->secret  = config('services.friendly_captcha.secret');
        $this->sitekey = config('services.friendly_captcha.sitekey');
        $this->verify  = config('services.friendly_captcha.verify_endpoint');
        $this->http    = new Client(config('services.friendly_captcha.options'));
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
