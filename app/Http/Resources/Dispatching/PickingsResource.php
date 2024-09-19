<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 22:40:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Dispatching;

use Illuminate\Http\Resources\Json\JsonResource;

class PickingsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'org_stock_code'      => $this->org_stock_code,
            'org_stock_name'      => $this->org_stock_name,
            // 'picker_name'         => $this->picker_name    ?? null,
            'picker'            => [
                'selected' => ($this->picker_id === null && $this->picker_name === null)
                    ? null
                    : [
                        'user_id'      => $this->picker_id ?? 0,
                        'contact_name' => $this->picker_name,
                    ],
                'pickerId'   => $this->picker_id,
                'pickerName' => $this->picker_name,
            ],
            'packer'            => [
                'selected' => ($this->packer_id === null && $this->packer_name === null)
                    ? null
                    : [
                        'user_id'      => $this->packer_id ?? 0,
                        'contact_name' => $this->packer_name,
                    ]
            ],
            // 'packer_name'         => $this->packer_name    ?? null,
            'vessel_picking'      => $this->vessel_picking            ?? null,
            'vessel_packing'      => $this->vessel_packing            ?? null,
            'picking_at'          => $this->picking_at                ?? null,
            'picked_at'           => $this->picked_at                 ?? null,
            'packing_at'          => $this->packing_at                ?? null,
            'packed_at'           => $this->packed_at                 ?? null,
            'quantity_required'   => intval($this->quantity_required) ?? 0,
            'quantity_picked'     => intval($this->quantity_picked)   ?? 0,
            'location_id'         => $this->location_id,

            'assign_picker'  => [
                'name'          => 'grp.models.picking.assign.picker',
                'parameters'    => [
                    'picking'   => $this->id
                    ]
                ],
            'assign_packer'  => [
                'name'          => 'grp.models.picking.assign.packer',
                'parameters'    => [
                    'picking'   => $this->id
                    ]
                ],
            'routes'         => [
                'pickingRoute'  => [
                    'name'          => 'grp.models.picking.state.picking',
                    'method'        => 'patch',
                    'parameters'    => [
                        'picking'   => $this->id
                    ]
                ],
                'queriedRoute'  => [
                    'name'          => 'grp.models.picking.state.queried',
                    'method'        => 'patch',
                    'parameters'    => [
                        'picking'   => $this->id
                    ]
                ],
                'waitingRoute'  => [
                    'name'          => 'grp.models.picking.state.waiting',
                    'method'        => 'patch',
                    'parameters'    => [
                        'picking'   => $this->id
                    ]
                ],
                'pickedRoute'  => [
                    'name'          => 'grp.models.picking.state.picked',
                    'method'        => 'patch',
                    'parameters'    => [
                        'picking'   => $this->id
                    ]
                ],
                'packingRoute'  => [
                    'name'          => 'grp.models.picking.state.packed',
                    'method'        => 'patch',
                    'parameters'    => [
                        'picking'   => $this->id
                    ]
                ],
                'doneRoute'  => [
                    'name'          => 'grp.models.picking.state.done',
                    'method'        => 'patch',
                    'parameters'    => [
                        'picking'   => $this->id
                    ]
                ],
            ]
        ];
    }
}
