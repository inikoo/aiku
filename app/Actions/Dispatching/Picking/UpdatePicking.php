<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Picking;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatching\Picking\PickingOutcomeEnum;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Enums\Dispatching\Picking\PickingVesselEnum;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Dispatching\Picking;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdatePicking extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    protected DeliveryNoteItem $deliveryNoteItem;

    public function handle(Picking $picking, array $modelData): Picking
    {
        data_set($modelData, 'picker_id', request()->user()->id);
        data_set($modelData, 'picked_at', now());
        data_set($modelData, 'state', PickingStateEnum::PICKED->value);

        return $this->update($picking, $modelData);
    }

    public function rules(): array
    {
        return [
            'state'                 => ['sometimes', Rule::enum(PickingStateEnum::class)],
            'outcome'               => ['sometimes', Rule::enum(PickingOutcomeEnum::class)],
            'vessel_picking'        => ['sometimes', Rule::enum(PickingVesselEnum::class)],
            'vessel_packing'        => ['sometimes', Rule::enum(PickingVesselEnum::class)]
        ];
    }

    public function asController(Picking $picking, ActionRequest $request): Picking
    {
        $this->initialisationFromShop($picking->shop, $request);

        return $this->handle($picking, $this->validatedData);
    }
}
