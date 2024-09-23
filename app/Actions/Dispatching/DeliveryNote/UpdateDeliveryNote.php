<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateDeliveryNotes;
use App\Actions\CRM\Customer\Hydrators\CustomerHydrateDeliveryNotes;
use App\Actions\Dispatching\DeliveryNote\Search\DeliveryNoteRecordSearch;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateDeliveryNotes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateDeliveryNotes;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithFixedAddressActions;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStatusEnum;
use App\Http\Resources\Dispatching\DeliveryNoteResource;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Helpers\Address;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;

class UpdateDeliveryNote extends OrgAction
{
    use WithActionUpdate;
    use WithFixedAddressActions;


    private DeliveryNote $deliveryNote;

    public function handle(DeliveryNote $deliveryNote, array $modelData): DeliveryNote
    {
        /** @var Address $deliveryAddressData */
        $deliveryAddressData = Arr::get($modelData, 'delivery_address');
        data_forget($modelData, 'delivery_address');

        $deliveryNote = $this->update($deliveryNote, $modelData, ['data']);
        $changes      =$deliveryNote->getChanges();

        if ($deliveryAddressData) {
            if ($deliveryNote->delivery_locked) {
                if ($deliveryNote->deliveryAddress->is_fixed) {
                    $deliveryNote = $this->updateFixedAddress(
                        $deliveryNote,
                        $deliveryNote->deliveryAddress,
                        $deliveryAddressData,
                        'Ordering',
                        'delivery',
                        'address_id'
                    );
                } else {
                    // todo remove non fixed address
                    $deliveryNote = $this->createFixedAddress(
                        $deliveryNote,
                        $deliveryAddressData,
                        'Ordering',
                        'delivery',
                        'address_id'
                    );
                }
            } else {
                UpdateAddress::run($deliveryNote->deliveryAddress, $deliveryAddressData->toArray());
            }
        }

        DeliveryNoteRecordSearch::dispatch($deliveryNote);

        if (Arr::hasAny($changes, ['type', 'state','status'])) {
            DeliveryNoteRecordSearch::dispatch($deliveryNote)->delay($this->hydratorsDelay);
            GroupHydrateDeliveryNotes::dispatch($deliveryNote->group)->delay($this->hydratorsDelay);
            OrganisationHydrateDeliveryNotes::dispatch($deliveryNote->organisation)->delay($this->hydratorsDelay);
            ShopHydrateDeliveryNotes::dispatch($deliveryNote->shop)->delay($this->hydratorsDelay);
            CustomerHydrateDeliveryNotes::dispatch($deliveryNote->customer)->delay($this->hydratorsDelay);

        }



        return $deliveryNote;
    }

    public function rules(): array
    {
        return [
            'reference'        => [
                'sometimes',
                'string',
                'max:64',
                new IUnique(
                    table: 'delivery_notes',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                        ['column' => 'id', 'value' => $this->deliveryNote->id, 'operator' => '!=']
                    ]
                ),
            ],
            'state'            => ['sometimes', 'required', new Enum(DeliveryNoteStateEnum::class)],
            'status'           => ['sometimes', 'required', new Enum(DeliveryNoteStatusEnum::class)],
            'email'            => ['sometimes', 'nullable', 'string', 'email'],
            'phone'            => ['sometimes', 'nullable', 'string'],
            'date'             => ['sometimes', 'date'],
            'delivery_address' => ['sometimes', 'required', new ValidAddress()],
            'last_fetched_at'  => ['sometimes', 'date'],
        ];
    }

    public function action(DeliveryNote $deliveryNote, array $modelData, int $hydratorsDelay = 0, bool $audit =true): DeliveryNote
    {
        if (!$audit) {
            DeliveryNote::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->deliveryNote   = $deliveryNote;

        $this->initialisationFromShop($deliveryNote->shop, $modelData);

        return $this->handle($deliveryNote, $this->validatedData);
    }

    public function jsonResponse(DeliveryNote $deliveryNote): DeliveryNoteResource
    {
        return new DeliveryNoteResource($deliveryNote);
    }
}
