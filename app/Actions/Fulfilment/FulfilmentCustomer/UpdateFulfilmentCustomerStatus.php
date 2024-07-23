<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 21:27:03 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Enums\Fulfilment\FulfilmentCustomer\FulfilmentCustomerStatus;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class UpdateFulfilmentCustomerStatus extends OrgAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): FulfilmentCustomer
    {
        $status    = FulfilmentCustomerStatus::NO_RENTAL_AGREEMENT;
        $createdAt = $fulfilmentCustomer->rentalAgreement->created_at;

        if($fulfilmentCustomer->rentalAgreement
            && $createdAt->lessThan($createdAt->addDays(30))) {
            $status = FulfilmentCustomerStatus::ACTIVE;
        }

        $fulfilmentCustomer->updateQuietly([
            'status' => $status
        ]);

        return $fulfilmentCustomer;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function asController(
        Organisation $organisation,
        Shop $shop,
        Fulfilment $fulfilment,
        FulfilmentCustomer $fulfilmentCustomer,
        ActionRequest $request
    ): FulfilmentCustomer {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(
        FulfilmentCustomer $fulfilmentCustomer,
        array $modelData
    ): FulfilmentCustomer {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


}
