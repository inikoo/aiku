<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_generic
 * @property string|null $contact_name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $wechat
 * @property string|null $whatsapp
 * @property int $number_products
 * @property int $number_families
 * @property int $number_without_family
 * @property int $number_missing_cbm
 * @property int $number_missing_lead_time
 * @property int $number_not_available
 * @property int|null $completeness
 * @property numeric $bought_amount group currency
 * @property int $number_purchase_orders
 * @property string $currency_code
 * @property array $families
 * @property array|null $contacts_update_route
 */
class AgentSuppliersDataQualityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                       => $this->id,
            'code'                     => $this->code,
            'name'                     => $this->name,
            'is_generic'               => $this->is_generic,
            'contacts'                 => [
                'contact_name' => $this->contact_name,
                'phone'        => $this->phone,
                'email'        => $this->email,
                'wechat'       => $this->wechat,
                'whatsapp'     => $this->whatsapp,
            ],
            'number_products'          => (int) $this->number_products,
            'number_families'          => (int) $this->number_families,
            'number_without_family'    => (int) $this->number_without_family,
            'number_missing_cbm'       => (int) $this->number_missing_cbm,
            'number_missing_lead_time' => (int) $this->number_missing_lead_time,
            'number_not_available'     => (int) $this->number_not_available,
            'completeness'             => $this->completeness !== null ? (int) $this->completeness : null,
            'bought_amount'            => (float) $this->bought_amount,
            'number_purchase_orders'   => (int) $this->number_purchase_orders,
            'currency_code'            => $this->currency_code,
            'families'                 => collect($this->families)->map(fn ($family) => [
                'id'                       => $family->stock_family_id,
                'code'                     => $family->code,
                'name'                     => $family->name,
                'number_products'          => (int) $family->number_products,
                'number_missing_cbm'       => (int) $family->number_missing_cbm,
                'number_missing_lead_time' => (int) $family->number_missing_lead_time,
                'number_not_available'     => (int) $family->number_not_available,
                'completeness'             => (int) $family->completeness,
                'defaults'                 => [
                    'minimum_carton_order'     => $family->minimum_carton_order,
                    'estimated_lead_time_days' => $family->estimated_lead_time_days,
                    'packaging_terms'          => json_decode($family->packaging_terms ?? '{}', true) ?: (object) [],
                    'production_calendar'      => json_decode($family->production_calendar ?? '{}', true) ?: (object) [],
                ],
                'update_route'             => $family->update_route,
            ])->all(),
            'contacts_update_route'    => $this->contacts_update_route,
        ];
    }
}
