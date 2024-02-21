<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\OrgAction;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class StorePalletToReturn extends OrgAction
{
    use AsCommand;

    public $commandSignature = 'pallet:store-to-return {palletReturn}';

    private PalletReturn $parent;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        foreach (Arr::get($modelData, 'pallets') as $pallet) {
            $pallet = Pallet::find($pallet);

            $pallet->update([
                'pallet_return_id' => $palletReturn->id
            ]);
        }

        // HydratePalletDeliveries::run($palletReturn);

        return $palletReturn;
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
            'pallets' => ['required', 'array']
        ];
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->parent = $palletReturn;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, array $modelData, int $hydratorsDelay = 0): PalletReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $palletReturn;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, $modelData);

        return $this->handle($palletReturn, $this->validatedData);
    }


    public function asCommand(Command $command): int
    {
        $palletReturn = PalletReturn::where('reference', $command->argument('palletDelivery'))->firstOrFail();

        $this->handle($palletReturn, [
            'group_id'               => $palletReturn->group_id,
            'organisation_id'        => $palletReturn->organisation_id,
            'fulfilment_id'          => $palletReturn->fulfilment_id,
            'fulfilment_customer_id' => $palletReturn->fulfilment_customer_id,
            'warehouse_id'           => $palletReturn->warehouse_id,
            'slug'                   => now()->timestamp
        ]);

        echo "Pallet created from delivery: $palletReturn->reference\n";

        return 0;
    }


    public function htmlResponse(PalletReturn $palletReturn, ActionRequest $request): RedirectResponse
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.pallet-returns.show', [
            'organisation'           => $palletReturn->organisation->slug,
            'fulfilment'             => $palletReturn->fulfilment->slug,
            'fulfilmentCustomer'     => $palletReturn->fulfilmentCustomer->slug,
            'palletReturn'           => $palletReturn->reference
        ]);
    }
}
