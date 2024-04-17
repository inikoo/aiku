<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 15:55:59 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

/**
 * @property string $number_payments
 * @property int $number_payment_accounts
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property string $name
 * @property string $state
 * @property int $org_id
 * @property int $id
 * @property mixed $org_slug
 * @property mixed $org_code
 * @property \App\Models\SysAdmin\Organisation $organisation
 *
 */
class SelectOrgPaymentServiceProvidersResource extends JsonResource
{
    public function toArray($request): array
    {
        $provider = Arr::get(explode('-', $this->code), 1);

        $additionalFields = match ($provider) {
            'checkout' => [
                'checkout_access_key' => [
                    'type'     => 'input',
                    'label'    => __('access key'),
                    'required' => true
                ],
                'checkout_secret_key' => [
                    'type'     => 'input',
                    'label'    => __('secret key'),
                    'required' => true
                ],
                'checkout_channel_id' => [
                    'type'     => 'input',
                    'label'    => __('channel id'),
                    'required' => true
                ]
            ],
            'bank' => [
                'bank_name' => [
                    'type'     => 'input',
                    'label'    => __('bank name'),
                    'required' => true
                ],
                'bank_account_name' => [
                    'type'     => 'input',
                    'label'    => __('bank account name'),
                    'required' => true
                ],
                'bank_account_id' => [
                    'type'     => 'input',
                    'label'    => __('bank account id'),
                    'required' => true
                ],
                'bank_swift_code' => [
                    'type'     => 'input',
                    'label'    => __('bank swift code'),
                    'required' => false
                ]
            ],
            default => []
        };

        $formData = [
            'blueprint' => [
                $provider != 'cash' ? [
                    'title'  => __('payment account'),
                    'fields' => [
                        'code' => [
                            'type'     => 'input',
                            'label'    => __('code'),
                            'required' => true
                        ],
                        'name' => [
                            'type'     => 'input',
                            'label'    => __('name'),
                            'required' => true
                        ],
                        ...$additionalFields
                    ]
                ] : []
            ],
            'route'      => [
                'name'       => 'grp.models.org.payment-account.store',
                'parameters' => [
                    'organisation' => $this->org_id
                ]
            ]
        ];

        return [
            'number_payments'             => $this->number_payments,
            'number_payment_accounts'     => $this->number_payment_accounts,
            'slug'                        => $this->slug,
            'org_slug'                    => $this->org_slug,
            'code'                        => $this->code,
            'org_code'                    => $this->org_code,
            'name'                        => $this->name,
            'state'                       => $this->state,
            'formData'                    => $formData
        ];
    }
}
