<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Actions\Comms\ShowCommsDashboard;
use App\Actions\Comms\WithCommsSubNavigation;
use App\Actions\Fulfilment\Fulfilment\UI\EditFulfilment;
use App\Actions\OrgAction;
use App\Http\Resources\Mail\OutboxResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Outbox;
use App\Models\Comms\PostRoom;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOutboxes extends OrgAction
{
    use WithCommsSubNavigation;
    private Shop|Organisation|PostRoom|Website|Fulfilment $parent;


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasAnyPermission([
            'shop-admin.'.$this->shop->id,
            'marketing.'.$this->shop->id.'.view',
            'web.'.$this->shop->id.'.view',
            'orders.'.$this->shop->id.'.view',
            'crm.'.$this->shop->id.'.view',
        ]);
    }


    public function handle(Shop|Organisation|PostRoom|Website|Fulfilment $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('outboxes.name', $value)
                    ->orWhere('outboxes.data', '=', $value);
            });
        });


        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Outbox::class);

        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('outboxes.shop_id', $parent->id);
        } elseif (class_basename($parent) == 'PostRoom') {
            $queryBuilder->where('outboxes.post_room_id', $parent->id);
        } elseif (class_basename($parent) == 'Website') {
            $queryBuilder->where('outboxes.website_id', $parent->id);
        } elseif (class_basename($parent) == 'Fulfilment') {
            $queryBuilder->where('outboxes.fulfilment_id', $parent->id);
        } else {
            $queryBuilder->where('outboxes.organisation_id', $parent->id);
        }

        return $queryBuilder
            ->defaultSort('outboxes.name')
            ->select(['outboxes.name', 'outboxes.slug', 'outboxes.data', 'post_rooms.id as post_rooms_id'])
            ->leftJoin('outbox_stats', 'outbox_stats.outbox_id', 'outboxes.id')
            ->leftJoin('post_rooms', 'post_room_id', 'post_rooms.id')
            ->allowedSorts(['name', 'data'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }
    // public function authorize(ActionRequest $request): bool
    // {
    //     return
    //         (
    //             $request->user()->tokenCan('root') or
    //             $request->user()->hasPermissionTo('mail.view')
    //         );
    // }

    public function jsonResponse(LengthAwarePaginator $outboxes): AnonymousResourceCollection
    {
        return OutboxResource::collection($outboxes);
    }


    public function htmlResponse(LengthAwarePaginator $outboxes, ActionRequest $request): Response
    {
        $subNavigation = null;
        if ($this->parent instanceof Shop){
            $subNavigation = $this->getCommsNavigation($this->organisation, $this->shop);
        }
        return Inertia::render(
            'Mail/Outboxes',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('outboxes '),
                'pageHead'    => [
                    'title' => __('outboxes'),
                    'subNavigation' => $subNavigation,
                ],
                'data'        => OutboxResource::collection($outboxes),


            ]
        )->table($this->tableStructure($this->parent));
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $website;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($website);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Shop $shop, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }

    /** @noinspection PhpUnused */
    // public function inPostRoom(PostRoom $postRoom, ActionRequest $request): LengthAwarePaginator
    // {
    //     $this->initialisation($request);
    //     return $this->handle($postRoom);
    // }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Outboxes'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };


        return match ($routeName) {
            'grp.org.shops.show.comms.outboxes.index' =>
            array_merge(
                ShowCommsDashboard::make()->getBreadcrumbs(
                    'grp.org.shops.show.comms.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.comms.outboxes.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.fulfilments.show.comms.dashboard' =>
            array_merge(
                EditFulfilment::make()->getBreadcrumbs(
                    'grp.org.fulfilments.show.setting.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.setting.outboxes.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
