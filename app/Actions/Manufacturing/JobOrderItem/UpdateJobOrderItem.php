<?php

namespace App\Actions\Manufacturing\JobOrderItem;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Manufacturing\JobOrderItem\JobOrderItemStateEnum;
use App\Enums\Manufacturing\JobOrderItem\JobOrderItemStatusEnum;
use App\Models\CRM\WebUser;
use App\Models\Manufacturing\JobOrderItem;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateJobOrderItem extends OrgAction
{
    use WithActionUpdate;


    private JobOrderItem $jobOrderItem;

    public function handle(JobOrderItem $jobOrderItem, array $modelData): JobOrderItem
    {
        $pallet = $this->update($jobOrderItem, $modelData);

        // if($pallet->wasChanged('state')) {

        //     if($pallet->pallet_delivery_id) {
        //         UpdatePalletDeliveryStateFromItems::run($pallet->palletDelivery);
        //     }
        //     if($pallet->pallet_return_id) {
        //         UpdatePalletReturnStateFromItems::run($pallet->palletReturn);
        //     }

        //     HydrateFulfilmentCustomer::dispatch($pallet->fulfilmentCustomer);
        //     FulfilmentCustomerHydratePallets::dispatch($pallet->fulfilmentCustomer);
        //     FulfilmentHydratePallets::dispatch($pallet->fulfilment);
        //     OrganisationHydratePallets::dispatch($pallet->organisation);
        //     WarehouseHydratePallets::dispatch($pallet->warehouse);
        // }

        return $jobOrderItem;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("productions-view.{$this->organisation->id}");
    }

    public function rules(): array
    {
        return [
            'status'             => [
                'sometimes',
                Rule::enum(JobOrderItemStatusEnum::class)
            ],
            'state'              => [
                'sometimes',
                Rule::enum(JobOrderItemStateEnum::class)
            ],
            'notes'              => ['sometimes', 'nullable', 'string', 'max:1024'],
            'quantity'           => ['sometimes', 'integer', 'min:1'],
            'received_at'        => ['sometimes', 'nullable', 'date'],
        ];
    }

    // public function fromRetina(Pallet $pallet, ActionRequest $request): Pallet
    // {
    //     /** @var FulfilmentCustomer $fulfilmentCustomer */
    //     $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
    //     $this->fulfilment   = $fulfilmentCustomer->fulfilment;
    //     $this->pallet       = $pallet;

    //     $this->initialisation($request->get('website')->organisation, $request);
    //     return $this->handle($pallet, $this->validatedData);
    // }

    public function asController(JobOrderItem $jobOrderItem, ActionRequest $request): JobOrderItem
    {
        $this->jobOrderItem = $jobOrderItem;
        $this->initialisation($jobOrderItem->organisation, $request);

        return $this->handle($jobOrderItem, $this->validatedData);
    }

    // public function fromApi(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, Pallet $pallet, ActionRequest $request): Pallet
    // {
    //     $this->pallet = $pallet;
    //     $this->initialisationFromFulfilment($pallet->fulfilment, $request);

    //     return $this->handle($pallet, $this->validatedData);
    // }

    public function action(JobOrderItem $jobOrderItem, array $modelData, int $hydratorsDelay = 0): JobOrderItem
    {
        $this->jobOrderItem         = $jobOrderItem;
        $this->asAction             = true;
        $this->hydratorsDelay       = $hydratorsDelay;
        $this->initialisation($jobOrderItem->organisation, $modelData);

        return $this->handle($jobOrderItem, $this->validatedData);
    }

    // public function jsonResponse(Pallet $pallet): PalletResource
    // {
    //     return new PalletResource($pallet);
    // }
}
