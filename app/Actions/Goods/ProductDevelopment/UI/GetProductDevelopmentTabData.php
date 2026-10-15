<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-25m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistItemStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistLevelEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\TradeUnit\TradeUnitMarketEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentChecklistItem;
use App\Models\Goods\ProductDevelopmentItem;
use App\Models\Goods\ProductDevelopmentMember;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductDevelopmentTabData
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function brief(ProductDevelopment $productDevelopment): array
    {
        $roleLabels   = ProductDevelopmentMemberRoleEnum::labels();
        $marketLabels = TradeUnitMarketEnum::labels();

        return [
            'brief'               => $productDevelopment->brief,
            'markets'             => collect($productDevelopment->markets)->map(fn (string $market) => $marketLabels[$market] ?? $market)->values()->all(),
            'target_landing_date' => $productDevelopment->target_landing_date?->toDateString(),
            'target_online_date'  => $productDevelopment->target_online_date?->toDateString(),
            'packaging_family'    => $productDevelopment->packagingFamily?->name ?? $productDevelopment->packagingFamily?->code,
            'members'             => $productDevelopment->members
                ->load('user')
                ->groupBy('user_id')
                ->map(fn ($memberships) => [
                    'name'  => $memberships->first()->user?->contact_name ?? $memberships->first()->user?->username,
                    'roles' => $memberships->map(fn (ProductDevelopmentMember $member) => $roleLabels[$member->role->value])->values()->all(),
                ])
                ->values()
                ->all(),
            'checklist'           => $this->checklistRows(
                $productDevelopment->checklistItems()
                    ->where('level', ProductDevelopmentChecklistLevelEnum::FAMILY->value)
                    ->orderBy('id')
                    ->get()
            ),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function products(ProductDevelopment $productDevelopment): array
    {
        $productDevelopment->load(['items.tradeUnit', 'items.supplierProduct', 'items.checklistItems']);

        return $productDevelopment->items->map(function (ProductDevelopmentItem $item) {
            $missing = $item->checklistItems->filter(fn (ProductDevelopmentChecklistItem $checklistItem) => $checklistItem->isMissing());

            return [
                'id'               => $item->id,
                'code'             => $item->tradeUnit->code,
                'name'             => $item->tradeUnit->name,
                'status'           => $item->tradeUnit->status->labels()[$item->tradeUnit->status->value],
                'route'            => [
                    'name'       => 'grp.trade_units.units.show',
                    'parameters' => ['tradeUnit' => $item->tradeUnit->slug],
                ],
                'supplier_product' => $item->supplierProduct?->code,
                'number_missing'   => $missing->count(),
                'missing_by_role'  => $missing->countBy(fn (ProductDevelopmentChecklistItem $checklistItem) => $checklistItem->role->value)->all(),
            ];
        })->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function compliance(ProductDevelopment $productDevelopment): array
    {
        $productDevelopment->load(['items.tradeUnit']);
        $checklistItems = $productDevelopment->checklistItems()
            ->where('role', ProductDevelopmentMemberRoleEnum::COMPLIANCE->value)
            ->orderBy('id')
            ->get()
            ->groupBy('product_development_item_id');

        return $productDevelopment->items->map(fn (ProductDevelopmentItem $item) => [
            'code'      => $item->tradeUnit->code,
            'name'      => $item->tradeUnit->name,
            'checklist' => $this->checklistRows($checklistItems->get($item->id, collect())),
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function checklistRows($checklistItems): array
    {
        $roleLabels  = ProductDevelopmentMemberRoleEnum::labels();
        $stateLabels = ProductDevelopmentChecklistItemStateEnum::labels();

        return collect($checklistItems)->map(fn (ProductDevelopmentChecklistItem $checklistItem) => [
            'id'          => $checklistItem->id,
            'label'       => $checklistItem->label,
            'role'        => $roleLabels[$checklistItem->role->value],
            'required'    => $checklistItem->required,
            'state'       => $checklistItem->state->value,
            'state_label' => $stateLabels[$checklistItem->state->value],
            'na_reason'   => $checklistItem->na_reason,
        ])->values()->all();
    }
}
