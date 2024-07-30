<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jan 2024 14:58:03 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\UI;

use App\Actions\CRM\Prospect\Mailshots\UI\IndexProspectMailshots;
use App\Actions\CRM\Prospect\Queries\UI\IndexProspectQueries;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Traits\WithProspectsSubNavigation;
use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Enums\UI\CRM\ProspectsTabsEnum;
use App\Http\Resources\CRM\ProspectQueriesResource;
use App\Http\Resources\CRM\ProspectsResource;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Mail\MailshotsResource;
use App\Http\Resources\Tag\TagResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Prospect;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\Tags\Tag;

class IndexProspects extends OrgAction
{
    use WithProspectsSubNavigation;

    private Shop|Organisation|Fulfilment $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("crm.{$this->shop->id}.prospects.edit");

        return  $request->user()->hasPermissionTo("crm.{$this->shop->id}.prospects.view");

    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(ProspectsTabsEnum::values());
        $this->parent = $fulfilment;

        return $this->handle($this->parent, 'prospects');
    }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request)->withTab(ProspectsTabsEnum::values());
        $this->parent = $shop;

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

    public function handle(Organisation|Shop $parent, $prefix = null): LengthAwarePaginator
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

        $query = QueryBuilder::for(Prospect::class);

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $query->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        if (class_basename($parent) == 'Shop') {
            $query->where('shop_id', $parent->id);
        }

        return $query
            ->defaultSort('prospects.name')
            ->with('shop')
            ->allowedSorts(['name', 'email', 'phone', 'contact_website'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Shop|Tag $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            if (class_basename($parent) != 'Tag') {
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
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title'       => __('No Prospects'),
                        'description' => null,
                        'count'       => $parent->crmStats->number_prospects
                    ]
                )
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
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

        return Inertia::render(
            'Org/Shop/CRM/Prospects',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'        => __('prospects'),
                'pageHead'     => [
                    'icon'    => ['fal', 'fa-user-plus'],
                    'title'   => __('prospects'),
                    'actions' => [
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
                    'subNavigation'    => $subNavigation,
                ],
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
                    'upload' => [
                        'name'       => 'org.models.shop.prospects.upload',
                        'parameters' => $this->parent->id
                    ],
                    'history' => [
                        'name'       => 'org.crm.prospects.uploads.history',
                        'parameters' => []
                    ],
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => ProspectsTabsEnum::navigation(),
                ],

                'tags' => TagResource::collection(Tag::all()),

                ProspectsTabsEnum::DASHBOARD->value => $this->tab == ProspectsTabsEnum::DASHBOARD->value ?
                    fn () => GetProspectsDashboard::run($this->parent, $request)
                    : Inertia::lazy(fn () => GetProspectsDashboard::run($this->parent, $request)),
                ProspectsTabsEnum::PROSPECTS->value => $this->tab == ProspectsTabsEnum::PROSPECTS->value ?
                    fn () => ProspectsResource::collection($prospects)
                    : Inertia::lazy(fn () => ProspectsResource::collection($prospects)),

                ProspectsTabsEnum::LISTS->value => $this->tab == ProspectsTabsEnum::LISTS->value ?
                    fn () => ProspectQueriesResource::collection(IndexProspectQueries::run(prefix: ProspectsTabsEnum::LISTS->value))
                    : Inertia::lazy(fn () => ProspectQueriesResource::collection(IndexProspectQueries::run(prefix: ProspectsTabsEnum::LISTS->value))),

                ProspectsTabsEnum::MAILSHOTS->value => $this->tab == ProspectsTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotsResource::collection(IndexProspectMailshots::run(shop: $this->parent, prefix: ProspectsTabsEnum::MAILSHOTS->value))
                    : Inertia::lazy(fn () => MailshotsResource::collection(IndexProspectMailshots::run(shop: $this->parent, prefix: ProspectsTabsEnum::MAILSHOTS->value))),
                ProspectsTabsEnum::HISTORY->value   => $this->tab == ProspectsTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run(model: Prospect::class, prefix: ProspectsTabsEnum::HISTORY->value))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run(model: Prospect::class, prefix: ProspectsTabsEnum::HISTORY->value))),


            ]
        )->table($this->tableStructure(parent: $this->parent, prefix: ProspectsTabsEnum::PROSPECTS->value))
            ->table(
                IndexProspectQueries::make()->tableStructure(
                    modelOperations: [
                        'createLink' => [
                            [
                                'route' => [
                                    'name'       => 'grp.org.shops.show.crm.prospects.lists.create',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ],
                                'label' => __('New list'),
                                'style' => 'primary'
                            ],
                        ]
                    ],
                    prefix: ProspectsTabsEnum::LISTS->value
                )
            )
            ->table(IndexProspectMailshots::make()->tableStructure(prefix: ProspectsTabsEnum::MAILSHOTS->value))
            ->table(IndexHistory::make()->tableStructure(prefix: ProspectsTabsEnum::HISTORY->value));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return [];

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
            'org.crm.prospects.index' =>
            array_merge(
                (new ShowCRMDashboard())->getBreadcrumbs(
                    'crm.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name' => 'org.crm.prospects.index',
                    ]
                ),
            ),
            'grp.org.shops.show.crm.prospects.index',
            'grp.org.shops.show.crm.prospects.uploads.index' =>
            array_merge(
                (new ShowCRMDashboard())->getBreadcrumbs(
                    'org.crm.shop.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.crm.prospects.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
