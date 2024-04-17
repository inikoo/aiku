<?php

namespace App\Actions\Accounting\PaymentGateway\Paypal\Traits;

use Illuminate\Support\Facades\Http;

trait WithPaypalConfiguration
{
    public function url(): string
    {
        if(env('PAYPAL_PRODUCTION')) {
            return env('PAYPAL_PRODUCTION_URL');
        }

        return env('PAYPAL_SANDBOX_URL');
    }

    public function headers(): array
    {
        return [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken()
        ];
    }

    public function getCredentials(): array
    {
        if(env('PAYPAL_PRODUCTION')) {
            return [
                'username' => env('PAYPAL_CLIENT_ID'),
                'password' => env('PAYPAL_CLIENT_SECRET')
            ];
        }

        return [
            'username' => env('PAYPAL_SANDBOX_CLIENT_ID'),
            'password' => env('PAYPAL_SANDBOX_CLIENT_SECRET')
        ];
    }

    public function getAccessToken(): string
    {
        $response = Http::asForm()
            ->withBasicAuth($this->getCredentials()['username'], $this->getCredentials()['password'])
            ->post($this->url() . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials'
        ]);

        return $response->json()['access_token'];
    }
}
