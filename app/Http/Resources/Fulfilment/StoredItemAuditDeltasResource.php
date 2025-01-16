<?php

/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-14h-07m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Resources\Fulfilment;

use Illuminate\Http\Resources\Json\JsonResource;

class StoredItemAuditDeltasResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                => $this->id,
            'pallet_id'                         => $this->pallet_id,
            'pallet_customer_reference'         => $this->pallet_customer_reference,
            'stored_item_id'                    => $this->stored_item_id,
            'stored_item_reference'             => $this->stored_item_reference,
            'audited_at'                        => $this->audited_at,
            'original_quantity'                 => $this->original_quantity,
            'audited_quantity'                  => $this->audited_quantity,
            'audit_type'                        => $this->audit_type,
            'state'                             => $this->state,
            'state_label'                       => $this->state->labels()[$this->state->value],
            'state_icon'                        => $this->state->stateIcon()[$this->state->value]
        ];
    }
}
