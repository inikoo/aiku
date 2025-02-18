<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-11h-54m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property string $customer_reference
 * @property string $state
 * @property string $status
 * @property string $notes
 * @property \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property \App\Models\Inventory\Location $location
 * @property \App\Models\Inventory\Warehouse $warehouse
 * @property \App\Models\Fulfilment\StoredItem $storedItems
 */
class RetinaDropshippingFulfilmentOrdersResources extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'shopify_order_id' => $this->shopify_order_id,
            'shopify_fulfilment_id' => $this->shopify_fulfilment_id,
            'reference' => $this->model->reference,
            'model' => class_basename($this->model),
            'state' => $this->model->state,
            'type' => $this->model->type,
            'slug' => $this->model->slug,
            'state_label'           => $this->model->state->labels()[$this->model->state->value],
            'state_icon'            => $this->model->state->stateIcon()[$this->model->state->value],
        ];
    }
}
