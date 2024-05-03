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
 * @property \App\Models\Market\Shop $shops
 * @property string $payment_service_provider_slug
 * @property string $payment_service_provider_code
 * @property string $payment_service_provider_name
 * @property string $slug
 * @property string $name
 * @property string $code
 * @property string $shop_name
 *
 */
class PaymentAccountsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'                          => $this->slug,
            'name'                          => $this->name,
            'payment_service_provider_slug' => $this->payment_service_provider_slug,
            'payment_service_provider_code' => $this->payment_service_provider_code,
            'payment_service_provider_name' => $this->payment_service_provider_name,
            'number_payments'               => $this->number_payments,
            'code'                          => $this->code,
            'shop_name'                     => $this->shop_name
        ];
    }
}
