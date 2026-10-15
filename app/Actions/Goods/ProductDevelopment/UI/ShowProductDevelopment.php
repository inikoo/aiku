<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-25m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\GetProductDevelopmentStageGate;
use App\Actions\Goods\ProductDevelopment\UI\Traits\WithProductDevelopmentsIndex;
use App\Actions\Helpers\History\UI\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStatusEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTypeEnum;
use App\Enums\UI\Goods\ProductDevelopmentTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Models\Goods\ProductDevelopment;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProductDevelopment extends OrgAction
{
    use WithProductDevelopmentAuthorisation;
    use WithProductDevelopmentsIndex {
        WithProductDevelopmentsIndex::getBreadcrumbs as getIndexBreadcrumbs;
    }

    public function handle(ProductDevelopment $productDevelopment): ProductDevelopment
    {
        return $productDevelopment->load(['stages', 'members', 'stats', 'owner', 'supplier', 'agent', 'tradeUnitFamily']);
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopment
    {
        $this->initialisationFromGroup(group(), $request)->withTab(ProductDevelopmentTabsEnum::values());

        return $this->handle($productDevelopment);
    }

    public function htmlResponse(ProductDevelopment $productDevelopment, ActionRequest $request): Response
    {
        $tabData = GetProductDevelopmentTabData::make();
        $gate    = GetProductDevelopmentStageGate::run($productDevelopment);
        $brand   = $productDevelopment->brand();

        $tab = fn (ProductDevelopmentTabsEnum $tabEnum, callable $resolver) => $this->tab === $tabEnum->value
            ? $resolver
            : Inertia::optional($resolver);

        return Inertia::render(
            'Goods/ProductDevelopment',
            [
                'title'       => $productDevelopment->code.' '.$productDevelopment->name,
                'breadcrumbs' => $this->getShowBreadcrumbs($productDevelopment),
                'pageHead'    => [
                    'icon'       => [
                        'title' => __('Product development'),
                        'icon'  => 'fal fa-lightbulb-on',
                    ],
                    'model'      => __('Product development'),
                    'title'      => $productDevelopment->code.' · '.$productDevelopment->name,
                    'afterTitle' => [
                        'label' => ProductDevelopmentStatusEnum::labels()[$productDevelopment->status->value],
                    ],
                ],
                'header'      => [
                    'type'               => ProductDevelopmentTypeEnum::labels()[$productDevelopment->type->value],
                    'status'             => $productDevelopment->status->value,
                    'status_icon'        => ProductDevelopmentStatusEnum::stateIcon()[$productDevelopment->status->value],
                    'brand'              => $brand?->name,
                    'family'             => $productDevelopment->tradeUnitFamily ? [
                        'code'  => $productDevelopment->tradeUnitFamily->code,
                        'name'  => $productDevelopment->tradeUnitFamily->name,
                        'route' => ['name' => 'grp.trade_units.families.show', 'parameters' => ['tradeUnitFamily' => $productDevelopment->tradeUnitFamily->slug]],
                    ] : null,
                    'supplier'           => $productDevelopment->supplier?->name,
                    'agent'              => $productDevelopment->agent?->name,
                    'owner'              => $productDevelopment->owner?->contact_name ?? $productDevelopment->owner?->username,
                    'target_online_date' => $productDevelopment->target_online_date?->toDateString(),
                    'number_items'       => (int) $productDevelopment->stats?->number_items,
                    'number_missing'     => (int) $productDevelopment->stats?->number_required_missing,
                ],
                'stages'      => GetProductDevelopmentStages::run($productDevelopment),
                'stageMove'   => [
                    'can_move'   => $gate['can_move'],
                    'reason'     => $gate['reason'],
                    'is_allowed' => $this->canMoveProductDevelopmentStage($request->user(), $productDevelopment),
                    'is_done'    => $productDevelopment->status === ProductDevelopmentStatusEnum::COMPLETED,
                    'route'      => [
                        'method'     => 'patch',
                        'name'       => 'grp.models.product_development.next_stage',
                        'parameters' => ['productDevelopment' => $productDevelopment->id],
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ProductDevelopmentTabsEnum::navigation(),
                ],

                ProductDevelopmentTabsEnum::BRIEF->value      => $tab(ProductDevelopmentTabsEnum::BRIEF, fn () => $tabData->brief($productDevelopment)),
                ProductDevelopmentTabsEnum::PRODUCTS->value   => $tab(ProductDevelopmentTabsEnum::PRODUCTS, fn () => $tabData->products($productDevelopment)),
                ProductDevelopmentTabsEnum::COMPLIANCE->value => $tab(ProductDevelopmentTabsEnum::COMPLIANCE, fn () => $tabData->compliance($productDevelopment)),
                ProductDevelopmentTabsEnum::ACTIVITY->value   => $tab(ProductDevelopmentTabsEnum::ACTIVITY, fn () => HistoryResource::collection(IndexHistory::run($productDevelopment, ProductDevelopmentTabsEnum::ACTIVITY->value))),
            ]
        )->table(IndexHistory::make()->tableStructure(prefix: ProductDevelopmentTabsEnum::ACTIVITY->value, model: $productDevelopment));
    }

    public function getShowBreadcrumbs(ProductDevelopment $productDevelopment): array
    {
        return array_merge(
            $this->getIndexBreadcrumbs(),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.product_developments.show',
                            'parameters' => ['productDevelopment' => $productDevelopment->slug],
                        ],
                        'label' => $productDevelopment->code,
                    ],
                ],
            ]
        );
    }
}
