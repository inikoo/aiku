<?php
/*
 * author Arya Permana - Kirin
 * created on 17-12-2024-11h-17m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Dispatching\Packing;

use App\Actions\OrgAction;
use App\Enums\Dispatching\Packing\PackingEngineEnum;
use App\Enums\Dispatching\Packing\PackingStateEnum;
use App\Enums\Dispatching\Picking\PickingNotPickedReasonEnum;
use App\Enums\Dispatching\Picking\PickingStateEnum;
use App\Enums\Dispatching\Picking\PickingEngineEnum;
use App\Models\Dispatching\DeliveryNoteItem;
use App\Models\Dispatching\Packing;
use App\Models\Dispatching\Picking;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePacking extends OrgAction
{
    use AsAction;
    use WithAttributes;

    protected Picking $picking;

    public function handle(Picking $picking, array $modelData): Packing
    {
        data_set($modelData, 'group_id', $picking->group_id);
        data_set($modelData, 'organisation_id', $picking->organisation_id);
        data_set($modelData, 'shop_id', $picking->shop_id);
        data_set($modelData, 'delivery_note_id', $picking->delivery_note_id);
        data_set($modelData, 'picking_id', $picking->id);

        return $picking->deliveryNoteItem->packings()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'state'           => ['sometimes', Rule::enum(PackingStateEnum::class)],
            'engine'          => ['sometimes', Rule::enum(PackingEngineEnum::class)],
            'quantity_packed' => ['sometimes', 'numeric'],
            'packer_id'       => [
                'sometimes',
                Rule::Exists('users', 'id')->where('group_id', $this->shop->group_id)
            ],
        ];
    }

    public function asController(Picking $picking, ActionRequest $request): Packing
    {
        $this->picking = $picking;
        $this->initialisationFromShop($picking->shop, $request);

        return $this->handle($picking, $this->validatedData);
    }

    public function action(Picking $picking, array $modelData): Packing
    {
        $this->picking = $picking;
        $this->initialisationFromShop($picking->shop, $modelData);

        return $this->handle($picking, $this->validatedData);
    }
}
