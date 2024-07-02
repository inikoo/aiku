<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 22:35:37 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Outbox\UI;

use App\Actions\Mail\ShowMailDashboard;
use App\Actions\OrgAction;
use App\Actions\UI\Marketing\MarketingHub;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Http\Resources\Mail\OutboxResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Mail\Outbox;
use App\Models\Mail\PostRoom;
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
    private Shop|Organisation|PostRoom|Website|Fulfilment $parent;

    public function handle(Shop|Organisation|PostRoom|Website|Fulfilment $parent, $prefix=null): LengthAwarePaginator
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

        $queryBuilder=QueryBuilder::for(Outbox::class);

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
            ->leftJoin('outbox_stats', 'outbox_stats.id', 'outbox_stats.outbox_id')
            ->leftJoin('post_rooms', 'post_room_id', 'post_rooms.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Mail') {
                    $query->where('outboxes.post_room_id', $parent->id);
                }
            })
            ->allowedSorts(['name', 'data'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'data', label: __('data'), canBeHidden: false, sortable: true, searchable: true);
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
        $scope     = $this->parent;
        // dd($outboxes);
        return Inertia::render(
            'Mail/Outboxes',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('outboxes '),
                'pageHead'    => [
                    'title'   => __('outboxes'),
                ],
                'data' => OutboxResource::collection($outboxes),


            ]
        )->table($this->tableStructure($this->parent));
    }


    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop);
    }

    public function inWebsite(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $website;
        $this->initialisationFromShop($shop, $request);
        return $this->handle($website);
    }

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
            // 'mail.outboxes.index' =>
            // array_merge(
            //     (new MarketingHub())->getBreadcrumbs(
            //         $routeName,
            //         $request->route()->originalParameters()
            //     ),
            //     $headCrumb()
            // ),
            'grp.org.shops.show.mail.outboxes' =>
            array_merge(
                ShowMailDashboard::make()->getBreadcrumbs( 'grp.org.shops.show.mail.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.mail.outboxes',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.fulfilments.show.mail.outboxes' =>
            array_merge(
                ShowMailDashboard::make()->getBreadcrumbs( 'grp.org.fulfilments.show.mail.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.mail.outboxes',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.shops.show.web.websites.outboxes' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs('Shop',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.websites.outboxes',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
