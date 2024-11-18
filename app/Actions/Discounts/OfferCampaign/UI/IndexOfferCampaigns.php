<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferCampaign\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Http\Resources\Catalogue\OfferCampaignsResource;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Discounts\OfferCampaign;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexOfferCampaigns extends OrgAction
{
    protected Shop $shop;

    public function handle(Shop $shop, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('offer_campaigns.code', $value)
                    ->orWhereWith('offer_campaigns.name', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(OfferCampaign::class);

        $query->where('offer_campaigns.shop_id', $shop->id);
        $query->leftjoin('shops', 'offer_campaigns.shop_id', '=', 'shops.id')
            ->leftJoin('offer_campaign_stats', 'offer_campaigns.id', 'offer_campaign_stats.offer_campaign_id');
        $query->defaultSort('offer_campaigns.id')
            ->select(
                'offer_campaigns.id',
                'offer_campaigns.slug',
                'offer_campaigns.code',
                'offer_campaigns.name',
                'offer_campaigns.type',
                'offer_campaigns.state',
                'offer_campaigns.status',
                'shops.slug as shop_slug',
                'offer_campaign_stats.number_current_offers as number_current_offers'
            );

        return $query->
            allowedSorts(['code', 'name', 'state', 'number_current_offers'])
            ->allowedFilters([$globalSearch, 'code', 'name'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop $shop, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $shop) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $emptyStateData = [
                'icons' => ['fal fa-comment-dollar'],
                'title' => __('No offer campaigns found'),
            ];


            $emptyStateData['description'] = __("There are no offer campaigns in this shop");


            $table->withGlobalSearch();


            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');
            $table->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'number_current_offers', label: __('Number Offers'), canBeHidden: false, sortable: true, searchable: true);
            $table->defaultSort('id');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("discounts.{$this->shop->id}.edit");

        return $request->user()->hasPermissionTo("discounts.{$this->shop->id}.view");
    }

    public function jsonResponse(LengthAwarePaginator $campaigns): AnonymousResourceCollection
    {
        return OfferCampaignsResource::collection($campaigns);
    }



    public function htmlResponse(LengthAwarePaginator $campaigns, ActionRequest $request): Response
    {
        $title      = __('Campaigns');
        $icon       = ['fal', 'fa-comment-dollar'];
        $afterTitle = null;
        $iconRight  = null;

        return Inertia::render(
            'Org/Shop/B2b/Campaigns/Campaigns',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Campaigns'),
                'pageHead'    => [
                    'title'      => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                    'icon'       => $icon,
                ],
                'data'        => OfferCampaignsResource::collection($campaigns),
            ]
        )->table($this->tableStructure($this->shop));
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->shop = $shop;
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
                        'label' => __('Offer Campaigns'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
