<?php

namespace App\Rules;

use App\Http\Integrations\FriendlyCaptcha\FriendlyCaptchaConnector as FriendlyCaptchaClient;
use Illuminate\Contracts\Validation\Rule;

class FriendlyCaptchaRule implements Rule
{
    protected $friendlyCaptchaClient;

    protected array $messages = [];

    /**
     * Constructor.
     */
    public function __construct(
        FriendlyCaptchaClient $friendlyCaptcha
    ) {
        $this->friendlyCaptchaClient = $friendlyCaptcha;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $response = $this->friendlyCaptchaClient->verifyResponse($value);

        if ($response->isSuccess()) {
            return true;
        }

        foreach ($response->getErrors() as $errorCode) {
            $this->messages[] = $this->mapErrorCodeToMessage($errorCode);
        }

        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return $this->messages;
    }

    /**
     * map FriendlyCaptcha error code to human readable validation message
     *
     * @var string $code
     */
    protected function mapErrorCodeToMessage(string $code): string
    {
        switch ($code) {
            case "secret_missing":
                return __('validation.secret_missing');
                break;
            case "secret_invalid":
                return __('validation.secret_invalid');
                break;
            case "solution_missing":
                return __('validation.solution_missing');
                break;
            case "bad_request":
                return __('validation.bad_request');
                break;
            case "solution_invalid":
                return __('validation.solution_invalid');
                break;
            case "solution_timeout_or_duplicate":
                return __('validation.solution_timeout_or_duplicate');
                break;
            default:
                return  __('validation.unexpected');
        }
    }
}
