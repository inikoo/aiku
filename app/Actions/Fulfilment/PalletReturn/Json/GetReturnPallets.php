<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Json;

use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use App\Models\CRM\WebUser;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class GetReturnPallets extends OrgAction
{
    public function handle(FulfilmentCustomer $fulfilmentCustomer): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('pallets.customer_reference', $value)
                    ->orWhereWith('pallets.reference', $value);
            });
        });


        $query = QueryBuilder::for(Pallet::class);

        $query->where('fulfilment_customer_id', $fulfilmentCustomer->id);
        $query->where('state', PalletStateEnum::STORING);
        $query->where('status', '!=', PalletStatusEnum::RETURNED);



        return $query->defaultSort('pallets.reference')
            ->allowedSorts(['customer_reference', 'pallets.reference'])
            ->allowedFilters([$globalSearch, 'customer_reference'])
            ->withPaginator(null)
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            return true;
        }

        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletsResource::collection($pallets);
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }

    public function fromRetina(ActionRequest $request): LengthAwarePaginator
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($fulfilmentCustomer);
    }

}
