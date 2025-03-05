<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Tiktok;

use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class AuthenticateTiktokAccount extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(Customer $customer, array $modelData)
    {
        $response = Http::get("https://auth.tiktok-shops.com/api/v2/token/get", [
            'app_key' => config('services.tiktok.client_id'),
            'app_secret' => config('services.tiktok.client_secret'),
            'auth_code' => Arr::get($modelData, 'code'),
            'grant_type' => 'authorized_code'
        ]);

        $data = json_decode($response->getBody(), true);

        if (isset($data['data']['access_token'])) {
            $userData = $data['data'];

            if (isset($userData)) {
                $userData = [
                    'id' => $userData['open_id'],
                    'name' => $userData['seller_name'],
                    'access_token' => $userData['access_token'],
                    'access_token_expire_in' => $userData['access_token_expire_in'],
                    'refresh_token' => $userData['access_token_expire_in'],
                    'refresh_token_expire_in' => $userData['refresh_token_expire_in'],
                ];

                // dd($userData);
            }

            // StoreTiktokUser::run();

            return redirect('/');
        }

        throw ValidationException::withMessages(['message' => __('tiktok.access_token')]);
    }

    public function redirectToTikTok()
    {
        $clientId = config('services.tiktok.client_id');
        $redirectUri = urlencode(config('services.tiktok.redirect_uri'));
        $state = uniqid();

        return "https://auth.tiktok-shops.com/oauth/authorize?app_key={$clientId}&state={$state}&redirect_uri={$redirectUri}";
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string']
        ];
    }

    public function asController(ActionRequest $request): void
    {
        $customer = $request->user()->customer;
        $this->initialisation($request);

        $this->handle($customer, $this->validatedData);
    }
}
