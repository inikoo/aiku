<?php

namespace App\Actions\Accounting\PaymentGateway\Paypal\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

trait WithPaypalConfiguration
{
    public function url(): string
    {
        if(app()->isProduction()) {
            return 'https://api-m.paypal.com';
        }

        return 'https://api-m.sandbox.paypal.com';
    }

    public function headers($clientId, $clientSecret): array
    {
        return [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken($clientId, $clientSecret)
        ];
    }

    public function getCredentials($clientId, $clientSecret): array
    {
        return [
            'username' => env($clientId),
            'password' => env($clientSecret)
        ];
    }

    public function getAccessToken($clientId, $clientSecret): string
    {
        $credentials = $this->getCredentials($clientId, $clientSecret);

        $response = Http::asForm()
            ->withBasicAuth(Arr::get($credentials, 'username'), Arr::get($credentials, 'password'))
            ->post($this->url() . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials'
        ]);

        return $response->json()['access_token'];
    }
}
