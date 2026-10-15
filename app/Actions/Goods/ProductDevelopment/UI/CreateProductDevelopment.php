<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\UI\Traits\WithProductDevelopmentsIndex;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTypeEnum;
use App\Enums\Goods\TradeUnit\TradeUnitMarketEnum;
use App\Models\Helpers\Brand;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateProductDevelopment extends OrgAction
{
    use WithProductDevelopmentAuthorisation;
    use WithProductDevelopmentsIndex;

    public function authorize(ActionRequest $request): bool
    {
        return $this->canCreateProductDevelopment($request->user());
    }

    public function asController(ActionRequest $request): Group
    {
        $this->initialisationFromGroup(group(), $request);

        return $this->group;
    }

    /**
     * @param array<string, string> $labels
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function enumOptions(array $labels): array
    {
        return collect($labels)->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])->values()->all();
    }

    public function htmlResponse(Group $group, ActionRequest $request): Response
    {
        return Inertia::render(
            'Goods/CreateProductDevelopment',
            [
                'breadcrumbs' => $this->getBreadcrumbs('grp.product_developments.create', __('New')),
                'title'       => __('New product development'),
                'pageHead'    => [
                    'title' => __('New product development'),
                    'icon'  => [
                        'title' => __('Product development'),
                        'icon'  => 'fal fa-lightbulb-on',
                    ],
                ],
                'currentUserId' => $request->user()->id,
                'options'     => [
                    'types'     => $this->enumOptions(ProductDevelopmentTypeEnum::labels()),
                    'roles'     => $this->enumOptions(ProductDevelopmentMemberRoleEnum::labels()),
                    'markets'   => $this->enumOptions(TradeUnitMarketEnum::labels()),
                    'users'     => User::where('group_id', $group->id)->where('status', true)->orderBy('contact_name')
                        ->get(['id', 'contact_name', 'username'])
                        ->map(fn (User $user) => ['value' => $user->id, 'label' => $user->contact_name ?: $user->username])
                        ->all(),
                    'brands'    => Brand::where('group_id', $group->id)->orderBy('name')->get(['id', 'name'])
                        ->map(fn (Brand $brand) => ['value' => $brand->id, 'label' => $brand->name])->all(),
                    'agents'    => Agent::where('group_id', $group->id)->orderBy('name')->get(['id', 'name'])
                        ->map(fn (Agent $agent) => ['value' => $agent->id, 'label' => $agent->name])->all(),
                    'suppliers' => Supplier::where('group_id', $group->id)->where('status', true)->orderBy('name')->get(['id', 'code', 'name', 'agent_id'])
                        ->map(fn (Supplier $supplier) => ['value' => $supplier->id, 'label' => $supplier->name.' ('.$supplier->code.')', 'agent_id' => $supplier->agent_id])->all(),
                ],
                'searchRoutes' => [
                    'trade_units'          => ['name' => 'grp.product_developments.json.search', 'parameters' => ['scope' => 'trade_units']],
                    'trade_unit_families'  => ['name' => 'grp.product_developments.json.search', 'parameters' => ['scope' => 'trade_unit_families']],
                    'packaging_families'   => ['name' => 'grp.product_developments.json.search', 'parameters' => ['scope' => 'packaging_families']],
                ],
                'storeRoute'  => [
                    'name'       => 'grp.models.product_development.store',
                    'parameters' => [],
                ],
            ]
        );
    }
}
