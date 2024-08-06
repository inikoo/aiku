<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\CRM\Customer\AddDeliveryAddressToCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\Search\FulfilmentCustomerRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Rules\ValidAddress;
use Lorisleiva\Actions\ActionRequest;

class AddDeliveryAddressToFulfilmentCustomer extends OrgAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): FulfilmentCustomer
    {

        AddDeliveryAddressToCustomer::make()->action($fulfilmentCustomer->customer, $modelData);
        $fulfilmentCustomer->refresh();

        FulfilmentCustomerRecordSearch::dispatch($fulfilmentCustomer);



        return $fulfilmentCustomer;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        return [
            'delivery_address'         => ['required', new ValidAddress()],
        ];
    }


    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): FulfilmentCustomer
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData, int $hydratorsDelay = 0, bool $strict = true): FulfilmentCustomer
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);


        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function jsonResponse(FulfilmentCustomer $fulfilmentCustomer): FulfilmentCustomerResource
    {
        return new FulfilmentCustomerResource($fulfilmentCustomer);
    }
}
