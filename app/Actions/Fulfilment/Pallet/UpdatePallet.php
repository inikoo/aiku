<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\Fulfilment\StoredItem\Hydrators\StoredItemHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Pallet;
use App\Models\SysAdmin\Organisation;
use App\Rules\AlphaDashDotSpaceSlashParenthesis;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePallet extends OrgAction
{
    use WithActionUpdate;


    private Pallet $pallet;

    public function handle(Pallet $pallet, array $modelData): Pallet
    {
        $pallet =  $this->update($pallet, $modelData, ['data']);

        StoredItemHydrateUniversalSearch::dispatch($pallet);

        return $pallet;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'customer_reference' => [
                'sometimes',
                'nullable',
                'max:64',
                new AlphaDashDotSpaceSlashParenthesis(),
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'pallets',
                    extraConditions: [
                        ['column' => 'customer_id', 'value' => $this->pallet->customer->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->pallet->id
                        ],
                    ]
                ),


            ],
            'state'              => [
                'sometimes',
                Rule::enum(PalletStateEnum::class)
            ],
            'status'               => [
                'sometimes',
                Rule::enum(PalletStatusEnum::class)
            ],
            'type'             => [
                'sometimes',
                Rule::enum(PalletTypeEnum::class)
            ],
            'notes'              => ['sometimes', 'string'],
            'received_at'        => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Pallet $pallet, ActionRequest $request): Pallet
    {
        $this->pallet=$pallet;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($pallet, $this->validateAttributes());
    }

    public function action(Pallet $pallet, array $modelData, int $hydratorsDelay = 0): Pallet
    {
        $this->pallet         =$pallet;
        $this->asAction       =true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($pallet->fulfilment, $modelData);
        return $this->handle($pallet, $this->validatedData);
    }

    public function jsonResponse(Pallet $pallet): StoredItemResource
    {
        return new StoredItemResource($pallet);
    }
}
