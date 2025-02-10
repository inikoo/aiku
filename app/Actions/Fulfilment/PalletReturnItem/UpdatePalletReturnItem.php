<?php
/*
 * author Arya Permana - Kirin
 * created on 10-02-2025-13h-08m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletReturnItem;

use App\Actions\Fulfilment\UI\WithFulfilmentAuthorisation;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Billables\Rental\RentalTypeEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturnItem;
use App\Models\Fulfilment\Space;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdatePalletReturnItem extends OrgAction
{
    use WithFulfilmentAuthorisation;
    use WithActionUpdate;

    public function handle(PalletReturnItem $palletReturnItem, array $modelData): PalletReturnItem
    {
        return $this->update($palletReturnItem, $modelData);
    }

    public function rules(): array
    {
        return [
            'quantity_picked'       => ['sometimes', 'numeric', 'min:0'],
            'quantity_dispatched'   => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function asController(PalletReturnItem $palletReturnItem, ActionRequest $request): PalletReturnItem
    {
        $this->initialisationFromFulfilment($palletReturnItem->palletReturn->fulfilment, $request);

        return $this->handle($palletReturnItem, $this->validatedData);
    }
}
