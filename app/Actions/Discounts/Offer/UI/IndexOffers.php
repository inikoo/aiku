<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\Offer\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Http\Resources\Catalogue\OffersResource;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferCampaign;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexOffers extends OrgAction
{
    protected Shop|OfferCampaign $parent;

    public function handle(Shop|OfferCampaign $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('offers.code', $value)
                    ->orWhereWith('offers.name', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(Offer::class);

        if($parent instanceof OfferCampaign) {
            $query->where('offers.offer_campaign_id', $parent->id);
        } else {
            $query->where('offers.shop_id', $parent->id);
        }
        $query->leftjoin('shops', 'offers.shop_id', '=', 'shops.id');
        $query->leftjoin('offer_campaigns', 'offers.offer_campaign_id', '=', 'offer_campaigns.id');

        $query->defaultSort('offers.id')
            ->select(
                'offers.id',
                'offers.slug',
                'offers.code',
                'offers.name',
                'offer_campaigns.slug as offer_campaign_slug',
                'shops.slug as shop_slug'
            );

        return $query->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch, 'code', 'name'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop|OfferCampaign $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $emptyStateData = [
                'icons' => ['fal fa-badge-percent'],
                'title' => __('No offers found'),
            ];


            $emptyStateData['description'] = __("There are no offers");


            $table->withGlobalSearch();


            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true);
            $table->defaultSort('id');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("discounts.{$this->shop->id}.edit");

        return $request->user()->hasPermissionTo("discounts.{$this->shop->id}.view");
    }

    public function jsonResponse(LengthAwarePaginator $offers): AnonymousResourceCollection
    {
        return OffersResource::collection($offers);
    }



    public function htmlResponse(LengthAwarePaginator $offers, ActionRequest $request): Response
    {
        $title      = __('Offers');
        $icon       = ['fal', 'fa-badge-percent'];
        $afterTitle = null;
        $iconRight  = null;

        return Inertia::render(
            'Org/Shop/B2b/Offers/Offers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Offers'),
                'pageHead'    => [
                    'title'      => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                    'icon'       => $icon,
                ],
                'data'        => OffersResource::collection($offers),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowShop::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters
                        ],
                        'label' => __('Offers'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}