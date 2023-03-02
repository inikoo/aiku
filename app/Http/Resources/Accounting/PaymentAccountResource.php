<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $number_payments
 * @property integer $payment_service_providers_slug
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 *
 */
class PaymentAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'payment_service_providers_slug' => $this->payment_service_providers_slug,
            'number_payments' => $this->number_payments,
            'slug' => $this->slug,
            'code' => $this->code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
