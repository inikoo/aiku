<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Hydrators;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistItemStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTargetFieldEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentChecklistItem;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductDevelopmentHydrateChecklist
{
    use AsAction;

    public function handle(ProductDevelopment $productDevelopment): void
    {
        $productDevelopment->load(['checklistItems.item.tradeUnit', 'checklistItems.item.supplierProduct']);

        foreach ($productDevelopment->checklistItems as $checklistItem) {
            $this->refreshTargetFieldState($checklistItem);
        }

        $missing = $productDevelopment->checklistItems->filter(fn (ProductDevelopmentChecklistItem $checklistItem) => $checklistItem->isMissing());

        $stats = [
            'number_items'            => $productDevelopment->items()->count(),
            'number_checklist_items'  => $productDevelopment->checklistItems->count(),
            'number_required_missing' => $missing->count(),
        ];

        foreach (ProductDevelopmentMemberRoleEnum::cases() as $role) {
            $stats[$role->statsColumn()] = $missing->where('role', $role)->count();
        }

        $productDevelopment->stats()->updateOrCreate([], $stats);
    }

    private function refreshTargetFieldState(ProductDevelopmentChecklistItem $checklistItem): void
    {
        $targetField = ProductDevelopmentTargetFieldEnum::tryFrom((string) $checklistItem->target_field);
        if (!$targetField || !$checklistItem->item || $checklistItem->state === ProductDevelopmentChecklistItemStateEnum::NOT_APPLICABLE) {
            return;
        }

        $isFilled = $targetField->isFilledOn($checklistItem->item->tradeUnit, $checklistItem->item->supplierProduct);
        $state    = $isFilled ? ProductDevelopmentChecklistItemStateEnum::COMPLETE : ProductDevelopmentChecklistItemStateEnum::MISSING;

        if ($checklistItem->state !== $state) {
            $checklistItem->update(['state' => $state]);
        }
    }
}
