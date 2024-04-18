<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 18 Apr 2024 16:36:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Accounting\Traits;

use Illuminate\Support\Arr;

trait HasPaymentServiceProviderFields
{
    public function blueprint($provider, $data = []): array
    {
        return match ($provider) {
            'checkout' => [
                'checkout_public_key' => [
                    'type'     => 'input',
                    'label'    => __('public key'),
                    'required' => true,
                    'value'    => Arr::get($data, 'checkout_public_key')
                ],
                'checkout_secret_key' => [
                    'type'     => 'input',
                    'label'    => __('secret key'),
                    'required' => true,
                    'value'    => Arr::get($data, 'checkout_secret_key')
                ],
                'checkout_channel_id' => [
                    'type'     => 'input',
                    'label'    => __('channel id'),
                    'required' => true,
                    'value'    => Arr::get($data, 'checkout_channel_id')
                ]
            ],
            'bank' => [
                'bank_name' => [
                    'type'     => 'input',
                    'label'    => __('bank name'),
                    'required' => true,
                    'value'    => Arr::get($data, 'bank_name')
                ],
                'bank_account_name' => [
                    'type'     => 'input',
                    'label'    => __('bank account name'),
                    'required' => true,
                    'value'    => Arr::get($data, 'bank_account_name')
                ],
                'bank_account_id' => [
                    'type'     => 'input',
                    'label'    => __('bank account id'),
                    'required' => true,
                    'value'    => Arr::get($data, 'bank_account_id')
                ],
                'bank_swift_code' => [
                    'type'     => 'input',
                    'label'    => __('bank swift code'),
                    'required' => false,
                    'value'    => Arr::get($data, 'bank_swift_code')
                ]
            ],
            'paypal' => [
                'paypal_client_id' => [
                    'type'     => 'input',
                    'label'    => __('client id'),
                    'required' => true,
                    'value'    => Arr::get($data, 'paypal_client_id')
                ],
                'paypal_client_secret' => [
                    'type'     => 'input',
                    'label'    => __('client secret'),
                    'required' => true,
                    'value'    => Arr::get($data, 'paypal_client_secret')
                ],
            ],
            default => []
        };
    }
}
