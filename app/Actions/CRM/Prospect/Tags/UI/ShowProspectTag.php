<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 08:55:04 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Tags\UI;

use App\Actions\CRM\Prospect\UI\IndexProspects;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\InertiaAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\Traits\WithProspectsSubNavigation;
use App\Enums\UI\CRM\ShowProspectTagTabsEnum;
use App\Http\Resources\CRM\ProspectsResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Prospect;
use App\Models\Helpers\Tag;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProspectTag extends InertiaAction
{
    use WithActionButtons;
    use WithProspectsSubNavigation;

    public Organisation|Shop $parent;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('crm.prospects.view');
    }

    public function handle(Tag $tag): Tag
    {
        return $tag;
    }

    public function inShop(Shop $shop, Tag $tag, ActionRequest $request): Tag
    {
        $this->parent = $shop;
        $this->initialisation($request)->withTab(ShowProspectTagTabsEnum::values());

        return $this->handle($tag);
    }

    public function htmlResponse(Tag $tag, ActionRequest $request): Response
    {
        $subNavigation = $this->getSubNavigation($request);

        return Inertia::render(
            'Prospects/ProspectTag',
            [
                'breadcrumbs'                             => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'                                   => $tag->name,
                'pageHead'                                => [
                    'title'         => $tag->name,
                    'subNavigation' => $subNavigation,
                    'icon'          => [
                        'tooltip' => __('tag'),
                        'icon'    => 'fal fa-tags'
                    ],
                    'actions'       => [
                        $this->getEditActionIcon($request),
                        $this->getDeleteActionIcon($request)
                    ],
                ],
                'tabs'                                    => [
                    'current'    => $this->tab,
                    'navigation' => ShowProspectTagTabsEnum::navigation()
                ],
                'tags'                                    => $tag,
                ShowProspectTagTabsEnum::PROSPECTS->value => $this->tab == ShowProspectTagTabsEnum::PROSPECTS->value ?
                    fn () => ProspectsResource::collection(Prospect::withAnyTagsOfAnyType($tag->name)->paginate())
                    : Inertia::lazy(fn () => ProspectsResource::collection(Prospect::withAnyTagsOfAnyType($tag->name)->paginate())),

                ShowProspectTagTabsEnum::HISTORY->value => $this->tab == ShowProspectTagTabsEnum::HISTORY->value
                    ?
                    fn () => HistoryResource::collection(
                        IndexHistory::run(
                            model: $tag,
                            prefix: ShowProspectTagTabsEnum::HISTORY->value
                        )
                    )
                    : Inertia::lazy(fn () => HistoryResource::collection(
                        IndexHistory::run(
                            model: $tag,
                            prefix: ShowProspectTagTabsEnum::HISTORY->value
                        )
                    )),
            ]
        )->table(IndexProspects::make()->tableStructure(parent: $tag, prefix: ShowProspectTagTabsEnum::PROSPECTS->value));
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, Tag $tag, array $routeParameters, string $suffix = null) {
            return [
                [
                    'type'           => $type,
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('tags')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $tag->label,
                        ],

                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $tag->label
                    ],
                    'suffix'         => $suffix
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.crm.prospects.tags.show',
            'grp.org.shops.show.crm.prospects.tags.edit' =>
            array_merge(
                IndexProspects::make()->getBreadcrumbs(
                    'grp.org.shops.show.crm.prospects.index',
                    $routeParameters
                ),
                $headCrumb(
                    'modelWithIndex',
                    Tag::firstWhere('tag_slug', $routeParameters['tag']),
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.crm.prospects.tags.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.crm.prospects.tags.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }
}
