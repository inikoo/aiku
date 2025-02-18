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
class PaymentAccountShopsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'shop_id'   => $this->shop_id,
            'shop_code' => $this->shop_code,
            'shop_name' => $this->shop_name,
            'shop_slug' => $this->shop_slug,
        ];
    }
}
