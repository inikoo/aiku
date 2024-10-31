<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Picking;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Dispatching\Picking;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdatePickingStateToPacking extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    protected DeliveryNoteItem $deliveryNoteItem;

    public function handle(Picking $picking, array $modelData): Picking
    {
        if (!$picking->packer_id) {
            data_set($modelData, 'picker_id', Arr::get($modelData, 'packer'));
        }
        if (!$picking->packer_assigned_at) {
            data_set($modelData, 'packer_assigned_at', now());
        }
        data_set($modelData, 'packing_at', now());
        data_set($modelData, 'state', PickingStateEnum::PACKING->value);

        return $this->update($picking, $modelData);
    }

    public function rules(): array
    {
        return [
            'packer' => ['sometimes', 'exists:employees,id'],
        ];
    }

    public function asController(Picking $picking, ActionRequest $request): Picking
    {
        $this->initialisationFromShop($picking->shop, $request);

        return $this->handle($picking, $this->validatedData);
    }

    public function action(Picking $picking, array $modelData): Picking
    {
        $this->initialisationFromShop($picking->shop, []);

        return $this->handle($picking, $modelData);
    }
}
