<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

 namespace App\Actions\Manufacturing\RawMaterial\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Manufacturing\Production\UI\ShowProductionCrafts;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Enums\UI\Manufacturing\ProductionTabsEnum;
use App\Enums\UI\Manufacturing\RawMaterialTabsEnum;
use App\Http\Resources\History\HistoryResource;

use App\Http\Resources\Manufacturing\ProductionResource;
use App\Http\Resources\Manufacturing\RawMaterialsResource;
use App\Http\Resources\Tag\TagResource;
use App\Models\Helpers\Tag;

use App\Models\Manufacturing\Production;
use App\Models\Manufacturing\RawMaterial;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRawMaterial extends OrgAction
{
    use WithActionButtons;

    public function handle(RawMaterial $rawMaterial): RawMaterial
    {
        return $rawMaterial;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);
        $this->canDelete = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);


        return $request->user()->hasAnyPermission([
            'org-supervisor.'.$this->organisation->id,
            'productions-view.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.view",
            "productions_operations.{$this->production->id}.orchestrate",
            "productions_rd.{$this->production->id}.view",
            "productions_procurement.{$this->production->id}.view",

        ]);
    }

    public function asController(Organisation $organisation, Production $production, RawMaterial $rawMaterial, ActionRequest $request): RawMaterial
    {
        $this->initialisationFromProduction($production, $request)->withTab(ProductionTabsEnum::values());

        return $this->handle($rawMaterial);
    }


    public function htmlResponse(RawMaterial $rawMaterial, ActionRequest $request): Response
    {

        return Inertia::render(
            'Org/Manufacturing/RawMaterial',
            [
                'title'                            => __('raw material'),
                'breadcrumbs'                      => $this->getBreadcrumbs($request->route()->originalParameters()),
                'navigation'                       => [
                    'previous' => $this->getPrevious($rawMaterial, $request),
                    'next'     => $this->getNext($rawMaterial, $request),
                ],
                'pageHead'                         => [
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'industry'],
                            'title' => __('raw material')
                        ],
                    'title'   => $rawMaterial->code,
                    'actions' => [
                        $this->canEdit ? $this->getEditActionIcon($request) : null,

                    ],


                ],
                'tabs'                             => [

                    'current'    => $this->tab,
                    'navigation' => RawMaterialTabsEnum::navigation(),
                ],
                'tagsList'      => TagResource::collection(Tag::all()),

                RawMaterialTabsEnum::SHOWCASE->value => $this->tab == RawMaterialTabsEnum::SHOWCASE->value ?
                    fn () => GetRawMaterialShowcase::run($rawMaterial)
                    : Inertia::lazy(fn () => GetRawMaterialShowcase::run($rawMaterial)),





               RawMaterialTabsEnum::HISTORY->value => $this->tab == RawMaterialTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($rawMaterial))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($rawMaterial)))

            ]
        )->table(IndexHistory::make()->tableStructure(prefix: ProductionTabsEnum::HISTORY->value));
    }


    public function jsonResponse(RawMaterial $rawMaterial): RawMaterialsResource
    {
        return new RawMaterialsResource($rawMaterial);
    }

    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $rawMaterial = RawMaterial::where('slug', $routeParameters['rawMaterial'])->first();

        return array_merge(
            ShowProductionCrafts::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.productions.show.crafts.raw_materials.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('raw materials'),
                            'icon'  => 'fal fa-bars',
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.productions.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => $rawMaterial?->code,
                            'icon'  => 'fal fa-bars'
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(RawMaterial $rawMaterial, ActionRequest $request): ?array
    {
        $previous = RawMaterial::where('code', '<', $rawMaterial->code)->where('organisation_id', $rawMaterial->organisation_id)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(RawMaterial $rawMaterial, ActionRequest $request): ?array
    {
        $next = RawMaterial::where('code', '>', $rawMaterial->code)->where('organisation_id', $rawMaterial->organisation_id)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?RawMaterial $rawMaterial, string $routeName): ?array
    {
        if (!$rawMaterial) {
            return null;
        }
    
        return match ($routeName) {
            'grp.org.productions.show.infrastructure.dashboard' => [
                'label' => $rawMaterial->cide,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'  => $this->organisation->slug,
                        'production'    => $rawMaterial->production->slug
                    ]
                ]
            ],
            default => null,
        };
    }
}
