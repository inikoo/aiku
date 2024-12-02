<?php
/*
 * author Arya Permana - Kirin
 * created on 02-12-2024-08h-58m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Comms\OrgPostRoom\UI;

use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\Catalogue\WithCollectionSubNavigation;
use App\Actions\Comms\ShowCommsDashboard;
use App\Actions\Comms\WithCommsSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Http\Resources\Catalogue\CollectionResource;
use App\Http\Resources\Mail\OrgPostRoomsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Shop;
use App\Models\Comms\OrgPostRoom;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexOrgPostRoom extends OrgAction
{
    use WithCommsSubNavigation;

    public function handle(Organisation $organisation, $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(OrgPostRoom::class);
        $queryBuilder->where('org_post_rooms.organisation_id', $organisation->id);

        $queryBuilder
            ->defaultSort('org_post_rooms.id')
            ->select([
                'org_post_rooms.id',
                'org_post_rooms.slug',
                'org_post_rooms.type',
                'org_post_rooms.name',
            ]);


        return $queryBuilder
            ->allowedSorts(['type', 'name'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(
        Organisation $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix . 'Page');
            }

            // foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            //     $table->elementGroup(
            //         key: $key,
            //         label: $elementGroup['label'],
            //         elements: $elementGroup['elements']
            //     );
            // }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No post room found"),
                            'count'       => $parent->commsStats->number_org_post_rooms,
                        ],
                        default => null
                    }
                );

            $table
            ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('Type'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $orgPostRooms): AnonymousResourceCollection
    {
        return OrgPostRoomsResource::collection($orgPostRooms);
    }

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


    public function htmlResponse(LengthAwarePaginator $orgPostRooms, ActionRequest $request): Response
    {
        $subNavigation = $this->getCommsNavigation($this->organisation, $this->shop);

        $title = __('Post Room');
        $icon  = [
            'icon'  => ['fal', 'fa-cube'],
            'title' => __('post rooms')
        ];
        $afterTitle = null;
        $iconRight = null;

        return Inertia::render(
            'Mail/OrgPostRooms',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'    => __('Post Rooms'),
                'pageHead' => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    // 'container'     => $container,
                    // 'actions'       => [
                    //     $this->canEdit && $request->route()->getName() == 'grp.org.shops.show.catalogue.collections.index' ? [
                    //         'type'    => 'button',
                    //         'style'   => 'create',
                    //         'tooltip' => __('new collection'),
                    //         'label'   => __('collection'),
                    //         'route'   => [
                    //             'name'       => 'grp.org.shops.show.catalogue.collections.create',
                    //             'parameters' => $request->route()->originalParameters()
                    //         ]
                    //     ] : false,
                    //     class_basename($this->parent) == 'Collection' ? [
                    //         'type'     => 'button',
                    //         'style'    => 'secondary',
                    //         'key'      => 'attach-collection',
                    //         'icon'     => 'fal fa-plus',
                    //         'tooltip'  => __('Attach collection to this collection'),
                    //         'label'    => __('Attach collection'),
                    //     ] : false
                    // ],
                    'subNavigation' => $subNavigation,
                ],
                // 'routes'        => $routes,
                'data'          => OrgPostRoomsResource::collection($orgPostRooms),
            ]
        )->table($this->tableStructure($this->organisation));
    }

    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($organisation);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Post Rooms'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.catalogue.collections.index' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.comms.post-rooms.index' =>
            array_merge(
                ShowCommsDashboard::make()->getBreadcrumbs(
                    'grp.org.shops.show.comms.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.comms.post-rooms.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),

            default => []
        };
    }
}
