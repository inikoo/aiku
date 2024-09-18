<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Picking;

use App\Actions\OrgAction;
use App\Enums\Dispatching\Picking\PickingOutcomeEnum;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Enums\Dispatching\Picking\PickingVesselEnum;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Dispatching\Picking;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePicking extends OrgAction
{
    use AsAction;
    use WithAttributes;

    protected DeliveryNoteItem $deliveryNoteItem;

    public function handle(DeliveryNoteItem $deliveryNoteItem, array $modelData): Picking
    {
        data_set($modelData, 'group_id', $deliveryNoteItem->group_id);
        data_set($modelData, 'organisation_id', $deliveryNoteItem->organisation_id);
        data_set($modelData, 'shop_id', $deliveryNoteItem->shop_id);
        data_set($modelData, 'delivery_note_id', $deliveryNoteItem->delivery_note_id);
        data_set($modelData, 'org_stock_id', $deliveryNoteItem->org_stock_id);

        $picking = $deliveryNoteItem->pickings()->create($modelData);

        return $picking;
    }

    public function rules(): array
    {
        return [
            'state'                 => ['sometimes', Rule::enum(PickingStateEnum::class)],
            'outcome'               => ['sometimes', Rule::enum(PickingOutcomeEnum::class)],
            'vessel_picking'        => ['sometimes', Rule::enum(PickingVesselEnum::class)],
            'vessel_packing'        => ['sometimes', Rule::enum(PickingVesselEnum::class)],
            'location_id'           => [
                'sometimes',
                Rule::Exists('locations', 'id')->where('warehouse_id', $this->deliveryNoteItem->deliveryNote->warehouse_id)
            ],
            'quantity_required'     => ['sometimes', 'numeric']
        ];
    }

    public function action(DeliveryNoteItem $deliveryNoteItem, array $modelData): Picking
    {
        $this->deliveryNoteItem = $deliveryNoteItem;
        $this->initialisationFromShop($deliveryNoteItem->shop, $modelData);
        return $this->handle($deliveryNoteItem, $this->validatedData);
    }
}
