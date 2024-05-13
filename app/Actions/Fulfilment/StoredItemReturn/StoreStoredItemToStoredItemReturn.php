<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemReturn;

use App\Actions\OrgAction;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use App\Models\Fulfilment\StoredItemReturn;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsCommand;

class StoreStoredItemToStoredItemReturn extends OrgAction
{
    use AsCommand;

    public $commandSignature = 'stored-item:store-to-return {storedItemReturn}';

    private StoredItemReturn $parent;

    public function handle(StoredItemReturn $storedItemReturn, array $modelData): StoredItemReturn
    {
        foreach (Arr::get($modelData, 'stored_items') as $key => $storedItem) {
            /** @var StoredItem $storedItemModel */
            $storedItemModel = StoredItem::find($key);
            $quantity        = $storedItemModel->pallets()->sum('quantity') - $storedItem['quantity'];

            $storedItemModel->pallets()->sync([
                $key => [
                    'quantity' => $quantity
                ]
            ]);

            if((int) $quantity === 0) {
                $storedItemModel->pallets()->detach([$key]);
            }
        }

        foreach (Arr::get($modelData, 'stored_items') as $key => $storedItem) {
            $quantity = $storedItemReturn->items()->where('stored_item_id', $key)->sum('quantity');
            $storedItemReturn->items()->syncWithoutDetaching([
                $key => [
                    'quantity' => $storedItem['quantity'] + $quantity
                ]
            ]);
        }

        return $storedItemReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'stored_items'            => ['required', 'array'],
            'stored_items.*.quantity' => ['required', 'integer', 'min:1']
        ];
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, StoredItemReturn $storedItemReturn, ActionRequest $request): StoredItemReturn
    {
        $this->parent = $storedItemReturn;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($storedItemReturn, $this->validatedData);
    }

    public function fromRetina(StoredItemReturn $storedItemReturn, ActionRequest $request): StoredItemReturn
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $this->parent       = $storedItemReturn;
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($storedItemReturn, $this->validatedData);
    }

    public function action(StoredItemReturn $storedItemReturn, array $modelData, int $hydratorsDelay = 0): StoredItemReturn
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->parent         = $storedItemReturn;
        $this->initialisationFromFulfilment($storedItemReturn->fulfilment, $modelData);

        return $this->handle($storedItemReturn, $this->validatedData);
    }


    public function asCommand(Command $command): int
    {
        $storedItemReturn = StoredItemReturn::where('reference', $command->argument('palletDelivery'))->firstOrFail();

        $this->handle($storedItemReturn, [
            'group_id'               => $storedItemReturn->group_id,
            'organisation_id'        => $storedItemReturn->organisation_id,
            'fulfilment_id'          => $storedItemReturn->fulfilment_id,
            'fulfilment_customer_id' => $storedItemReturn->fulfilment_customer_id,
            'warehouse_id'           => $storedItemReturn->warehouse_id,
            'slug'                   => now()->timestamp
        ]);

        echo "Pallet created from delivery: $storedItemReturn->reference\n";

        return 0;
    }


    public function htmlResponse(StoredItemReturn $storedItemReturn, ActionRequest $request): RedirectResponse
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.fulfilment-customer.stored-item-return.stored-item.store' => Redirect::route('grp.org.fulfilments.show.crm.customers.show.stored-item-returns.show', [
                'organisation'               => $storedItemReturn->organisation->slug,
                'fulfilment'                 => $storedItemReturn->fulfilment->slug,
                'fulfilmentCustomer'         => $storedItemReturn->fulfilmentCustomer->slug,
                'storedItemReturn'           => $storedItemReturn->slug
            ]),
            default => Redirect::route('retina.storage.pallet-returns.show', [
                'storedItemReturn'     => $storedItemReturn->slug
            ])
        };
    }
}
