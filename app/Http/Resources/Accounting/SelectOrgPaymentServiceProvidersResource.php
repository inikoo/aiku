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
        return [
            'number_payments'             => $this->number_payments,
            'number_payment_accounts'     => $this->number_payment_accounts,
            'slug'                        => $this->slug,
            'org_slug'                    => $this->org_slug,
            'code'                        => $this->code,
            'org_code'                    => $this->org_code,
            'name'                        => $this->name,
            'storeRoute'                  => [
                'name'       => 'grp.models.org.payment-service-provider.store',
                'parameters' => [
                    'organisation'           => $this->organisation->id,
                    'paymentServiceProvider' => $this->id
                ]
            ]
        ];
    }
}
