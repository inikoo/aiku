<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:30:19 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $number
 * @property string $slug
 * @property string $date
 * @property mixed $parent_name
 * @property mixed $reference
 * @property mixed $parent_type
 * @property mixed $state
 * @property mixed $org_currency_code
 * @property mixed $parent_slug
 * @property mixed $org_total_cost
 * @property mixed $supplier_purchase_orders
 * @property mixed $organisation_name
 * @property mixed $organisation_code
 * @property mixed $organisation_slug
 * @property mixed $quantity_ordered
 * @property mixed $org_net_amount
 * @property int|null $number_pending_price_changes
 */
class PurchaseOrdersResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'reference'         => $this->reference,
            'state'             => $this->state,
            'state_label'       => $this->state->labels()[$this->state->value],
            'state_icon'        => $this->state->stateIcon()[$this->state->value],
            'parent_type'       => $this->parent_type,
            'parent_name'       => $this->parent_name,
            'parent_slug'       => $this->parent->slug,
            'agent_name'        => $this->agent_id && $this->relationLoaded('agent') ? $this->agent?->name : null,
            'agent_slug'        => $this->agent_id && $this->relationLoaded('agent') ? $this->agent?->slug : null,
            'org_agent_slug'    => $this->agent_id && $this->parent_type === 'OrgSupplier' && $this->parent && $this->parent->relationLoaded('orgAgent') ? $this->parent->orgAgent?->slug : null,
            'supplier_slug'     => $this->parent_type === 'OrgSupplier' && $this->parent && $this->parent->relationLoaded('supplier') ? $this->parent->supplier->slug : null,
            'slug'              => $this->slug,
            'number_current_purchase_order_transactions'   => $this->number_current_purchase_order_transactions,
            'quantity_ordered'  => $this->quantity_ordered,
            'date'              => $this->date,
            'org_currency_code'     => $this->org_currency_code,
            'org_net_amount'    => $this->org_net_amount,
            'org_total_cost'    => $this->org_total_cost,
            'supplier_purchase_orders' => $this->supplier_purchase_orders,
            'organisation_name' => $this->organisation_name,
            'organisation_code' => $this->organisation_code,
            'organisation_slug' => $this->organisation_slug,
            'number_pending_price_changes' => (int) $this->number_pending_price_changes,
        ];
    }

}
