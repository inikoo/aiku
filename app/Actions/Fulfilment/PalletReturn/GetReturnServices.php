<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 May 2024 09:45:43 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Http\Resources\Fulfilment\ServicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Service;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class GetReturnServices extends OrgAction
{
    protected function getElementGroups(Fulfilment $parent): array
    {

        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    ServicestateEnum::labels(),
                    ServicestateEnum::count($parent->shop),
                    ServicestateEnum::shortLabels(),
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(Fulfilment $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('services.name', $value)
                    ->orWhereStartWith('services.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Service::class);
        $queryBuilder->where('services.shop_id', $parent->shop_id);
        $queryBuilder->where('services.is_auto_assign', false);
        $queryBuilder->join('assets', 'services.asset_id', '=', 'assets.id');
        $queryBuilder->join('currencies', 'assets.currency_id', '=', 'currencies.id');


        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

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
            ->withPaginator($prefix)
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

    public function asController(Organisation $organisation, Fulfilment $fulfilment, PalletReturn $palletReturn, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(ServicesTabsEnum::values());

        return $this->handle($fulfilment, ServicesTabsEnum::SERVICES->value);
    }

    public function jsonResponse(LengthAwarePaginator $services): AnonymousResourceCollection
    {
        return ServicesResource::collection($services);
    }
}
