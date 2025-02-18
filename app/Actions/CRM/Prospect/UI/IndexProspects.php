<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jan 2024 14:58:03 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Helpers\History\UI\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\Traits\WithProspectsSubNavigation;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\UI\CRM\ProspectsTabsEnum;
use App\Http\Resources\CRM\ProspectsResource;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Tag\TagResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Prospect;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Helpers\Tag;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexProspects extends OrgAction
{
    use WithProspectsSubNavigation;

    private Group|Shop|Organisation|Fulfilment $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Shop) {
            $this->canEdit = $request->user()->authTo("crm.{$this->shop->id}.prospects.edit");

            return $this->canEdit = $request->user()->authTo("crm.{$this->shop->id}.prospects.view");
        } elseif ($this->parent instanceof Fulfilment) {
            $this->canEdit = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");

            return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif ($this->parent instanceof Group) {
            return $request->user()->authTo("group-overview");
        }

        return false;
    }


    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->tab    = ProspectsTabsEnum::PROSPECTS->value;
        $this->parent = group();
        $tabs         = ProspectsTabsEnum::values();
        unset($tabs[array_search(ProspectsTabsEnum::DASHBOARD->value, $tabs)]);
        $this->initialisationFromGroup(group(), $request)->withTab($tabs);

        return $this->handle(parent: $this->parent, prefix: ProspectsTabsEnum::PROSPECTS->value);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(ProspectsTabsEnum::values());

        return $this->handle($this->parent, 'prospects');
    }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(ProspectsTabsEnum::values());

        return $this->handle($shop, 'prospects');
    }

    protected function getElementGroups($parent): array
    {
        return
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        ProspectStateEnum::labels(),
                        ProspectStateEnum::count($parent)
                    ),
                    'engine'   => function ($query, $elements) {
                        $query->whereIn('prospects.state', $elements);
                    }
                ]
            ];
    }

    public function handle(Group|Organisation|Shop|Fulfilment|Tag $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('prospects.name', $value)
                    ->orWhereWith('prospects.email', $value)
                    ->orWhereWith('prospects.phone', $value)
                    ->orWhereWith('prospects.contact_website', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Prospect::class);

        if ($parent instanceof Organisation or $parent instanceof Shop) {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        if ($parent instanceof Shop) {
            $queryBuilder->where('prospects.shop_id', $parent->id);
        } elseif ($parent instanceof Fulfilment) {
            $queryBuilder->where('prospects.shop_id', $parent->shop_id);
        } elseif ($parent instanceof Organisation) {
            $queryBuilder->where('prospects.organisation_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $queryBuilder->where('prospects.group_id', $parent->id);
        } elseif ($parent instanceof Tag) {
            $queryBuilder->leftJoin('taggables', 'taggables.tag_id', '=', 'tags.id')
                 ->where('taggables.taggable_id', $parent->id)
                 ->where('taggables.taggable_type', 'Prospect');
        }

        return $queryBuilder
            ->defaultSort('prospects.name')
            ->allowedSorts(['name', 'email', 'phone', 'contact_website'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group|Organisation|Shop|Fulfilment|Tag $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            if (class_basename($parent) != 'Tag' and !($parent instanceof Group)) {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch();
            if (!($parent instanceof Group)) {
                $table->withEmptyState(
                    [
                        'title'       => __('No Prospects'),
                        'description' => null,
                        'count'       => $parent->crmStats->number_prospects
                    ]
                );
            }
            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'email', label: __('email'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'phone', label: __('phone'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'website', label: __('website'), canBeHidden: false, sortable: true, searchable: true);

            if (class_basename($parent) != 'Tag') {
                $table->column(key: 'tags', label: __('tags'), canBeHidden: false, sortable: true, searchable: true);
            }
        };
    }

    public function jsonResponse(LengthAwarePaginator $prospects): AnonymousResourceCollection
    {
        return ProspectsResource::collection($prospects);
    }


    public function htmlResponse(LengthAwarePaginator $prospects, ActionRequest $request): Response
    {
        $subNavigation = $this->getSubNavigation($request);
        $dataProspect  = [
            'data' => $this->tab == ProspectsTabsEnum::PROSPECTS->value
                ? ProspectsResource::collection($prospects)
                : Inertia::lazy(fn () => ProspectsResource::collection($prospects)),

            'tagRoute' => [
                'store'  => [
                    'name'       => 'grp.models.prospect.tag.store',
                    'parameters' => [],
                ],
                'update' => [
                    'name'       => 'grp.models.prospect.tag.attach',
                    'parameters' => [],
                ],
            ],

            'tagsList' => TagResource::collection(Tag::where('type', 'crm')->get()),
        ];

        $tabs = [
            'tabs' => [
                'current'    => $this->tab,
                'navigation' => ProspectsTabsEnum::navigation(),
            ],

            ProspectsTabsEnum::DASHBOARD->value => $this->tab == ProspectsTabsEnum::DASHBOARD->value ?
                fn () => GetProspectsDashboard::run($this->parent, $request)
                : Inertia::lazy(fn () => GetProspectsDashboard::run($this->parent, $request)),
            ProspectsTabsEnum::PROSPECTS->value => $this->tab == ProspectsTabsEnum::PROSPECTS->value ?
                fn () => $dataProspect
                : Inertia::lazy(fn () => $dataProspect),
            ProspectsTabsEnum::HISTORY->value   => $this->tab == ProspectsTabsEnum::HISTORY->value ?
                fn () => HistoryResource::collection(IndexHistory::run(model: Prospect::class, prefix: ProspectsTabsEnum::HISTORY->value))
                : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run(model: Prospect::class, prefix: ProspectsTabsEnum::HISTORY->value))),
        ];

        if ($this->parent instanceof Group) {
            $subNavigation = null;
            $tabs          = [
                'tabs'                              => [
                    'current'    => $this->tab,
                    'navigation' => Arr::except(ProspectsTabsEnum::navigation(), [ProspectsTabsEnum::DASHBOARD->value]),
                ],
                ProspectsTabsEnum::PROSPECTS->value => $this->tab == ProspectsTabsEnum::PROSPECTS->value ?
                    fn () => $dataProspect
                    : Inertia::lazy(fn () => $dataProspect),
            ];
        }


        return Inertia::render(
            'Org/Shop/CRM/Prospects',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'        => __('prospects'),
                'pageHead'     => array_filter([
                    'icon'          => ['fal', 'fa-user-plus'],
                    'title'         => __('prospects'),
                    'actions'       => [
                        $this->canEdit ? [
                            'type'    => 'buttonGroup',
                            'buttons' =>
                                match (class_basename($this->parent)) {
                                    'Shop' => [
                                        [
                                            'style' => 'primary',
                                            'icon'  => ['fal', 'fa-upload'],
                                            'label' => 'upload',
                                            'route' => [
                                                'name'       => 'grp.org.models.shop.prospects.upload',
                                                'parameters' => $this->parent->id

                                            ],
                                        ],
                                        [
                                            'type'  => 'button',
                                            'style' => 'create',
                                            'label' => __('prospect'),
                                            'route' => [
                                                'name'       => 'grp.org.shops.show.prospects.create',
                                                'parameters' => $request->route()->originalParameters()
                                            ]
                                        ]
                                    ],
                                    default => []
                                }


                        ] : false
                    ],
                    'subNavigation' => $subNavigation,
                ]),
                'uploads'      => [
                    'templates' => [
                        'routes' => [
                            'name' => 'org.downloads.templates.prospects'
                        ]
                    ],
                    'event'     => class_basename(Prospect::class),
                    'channel'   => 'uploads.org.'.request()->user()->id
                ],
                'uploadRoutes' => [
                    'upload'  => [
                        'name'       => 'org.models.shop.prospects.upload',
                        'parameters' => $this->parent->id
                    ],
                    'history' => [
                        'name'       => 'org.crm.prospects.uploads.history',
                        'parameters' => []
                    ],
                ],
                ...$tabs

            ]
        )->table($this->tableStructure(parent: $this->parent, prefix: ProspectsTabsEnum::PROSPECTS->value))
            ->table(IndexHistory::make()->tableStructure(prefix: ProspectsTabsEnum::HISTORY->value));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Prospects'),
                        'icon'  => 'fal fa-transporter'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.crm.prospects.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.crm.prospects.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.overview.crm.prospects.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => 'grp.overview.crm.prospects.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),

            default => []
        };
    }
}
