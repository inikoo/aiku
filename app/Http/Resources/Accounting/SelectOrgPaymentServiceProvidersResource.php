<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 15:55:59 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

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
        $provider = explode('-', $this->slug);

        $additionalFields = match ($provider[1]) {
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
            default => []
        };

        $formData = [
            'blueprint' => [
                [
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
                ]
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
