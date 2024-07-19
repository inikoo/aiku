<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jul 2024 13:54:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\Json;

use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\ServicesResource;
use App\Models\Catalogue\Service;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetFulfilmentServices extends OrgAction
{
    public function handle(Fulfilment $parent, PalletDelivery|PalletReturn $scope): LengthAwarePaginator
    {

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {

            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('services.name', $value)
                    ->orWhereStartWith('services.code', $value);
            });
        });



        $queryBuilder = QueryBuilder::for(Service::class);
        $queryBuilder->where('services.shop_id', $parent->shop_id);
        $queryBuilder->where('services.is_auto_assign', false);
        $queryBuilder->join('assets', 'services.asset_id', '=', 'assets.id');
        $queryBuilder->join('currencies', 'assets.currency_id', '=', 'currencies.id');

        $queryBuilder->whereNotIn('services.asset_id', $scope->services()->pluck('asset_id'));

        $queryBuilder
            ->defaultSort('services.id')
            ->select([
                'services.id',
                'services.slug',
                'services.state',
                'services.created_at',
                'services.price',
                'services.unit',
                'assets.name',
                'assets.code',
                'assets.current_historic_asset_id as historic_asset_id',
                'services.description',
                'currencies.code as currency_code',
                'services.is_auto_assign',
                'services.auto_assign_trigger',
                'services.auto_assign_subject',
                'services.auto_assign_subject_type',
                'services.auto_assign_status',
            ]);


        return $queryBuilder->allowedSorts(['code','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator(null)
            ->withQueryString();
    }

    //todo review this
    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof WebUser) {
            return true;
        }

        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    public function inPalletDelivery(Fulfilment $fulfilment, PalletDelivery $scope, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, $scope);
    }

    public function inPalletReturn(Fulfilment $fulfilment, PalletReturn $scope, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, $scope);
    }

    public function jsonResponse(LengthAwarePaginator $services): AnonymousResourceCollection
    {
        return ServicesResource::collection($services);
    }
}
