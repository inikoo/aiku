<?php

/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $number_payments
 * @property \App\Models\Catalogue\Shop $shops
 * @property string $payment_service_provider_slug
 * @property string $payment_service_provider_code
 * @property string $payment_service_provider_name
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $code
 * @property string $shop_name
 * @property string $shop_id
 *
 */
class PaymentAccountsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                            => $this->id,
            'slug'                          => $this->slug,
            'name'                          => $this->name,
            'payment_service_provider_slug' => $this->payment_service_provider_slug,
            'payment_service_provider_code' => $this->payment_service_provider_code,
            'payment_service_provider_name' => $this->payment_service_provider_name,
            'number_payments'               => $this->number_payments,
            'number_pas'                    => $this->number_pas,
            'number_pas_state_active'       => $this->number_pas_state_active,
            'code'                          => $this->code,
            'number_shop'                   => $this->number_shop,
            'organisation_name'             => $this->organisation_name,
            'organisation_slug'             => $this->organisation_slug,
        ];
    }
}
