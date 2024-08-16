<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 16:54:48 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\OrgAction;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsCommand;

class StoreStoredItemToReturn extends OrgAction
{
    use AsCommand;

    private PalletReturn $parent;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        return $palletReturn;
    }


    public function rules(): array
    {
        return [
            'quantity'  => ['required', 'numeric', 'min:0'],
            'reference' => [
                'required',
                'string',
                Rule::exists('stored_items', 'reference')->where(function ($query) {
                    $query->where('fulfilment_customer_id', $this->parent->fulfilment_customer_id);
                })
            ],
        ];
    }


    public function action(PalletReturn $palletReturn, array $modelData, int $hydratorsDelay = 0): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }


}
