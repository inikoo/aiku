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
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentTargetFieldEnum;
use App\Enums\Goods\TradeUnit\TradeUnitMarketEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentChecklistItem;
use App\Models\Goods\ProductDevelopmentItem;
use App\Models\Goods\ProductDevelopmentMember;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductDevelopmentTabData
{
    use AsAction;

    private bool $canSeeCosts = true;

    /**
     * @return array<string, mixed>
     */
    public function brief(ProductDevelopment $productDevelopment, ?User $viewer = null, bool $canSeeCosts = true): array
    {
        $this->canSeeCosts = $canSeeCosts;
        $canOpenProfiles = (bool) $viewer?->authTo('sysadmin.view');
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
                ->sortBy(fn (ProductDevelopmentMember $member) => array_search($member->role, ProductDevelopmentMemberRoleEnum::cases(), true))
                ->map(fn (ProductDevelopmentMember $member) => [
                    'id'          => $member->id,
                    'user_id'     => $member->user_id,
                    'name'        => $member->user?->contact_name ?: $member->user?->username,
                    'username'    => $member->user?->username,
                    'avatar'      => $member->user?->image_id ? $member->user->imageSources(48, 48) : null,
                    'profile_url' => $canOpenProfiles && $member->user ? route('grp.sysadmin.users.show', ['user' => $member->user->slug]) : null,
                    'role'   => [
                        'label' => $roleLabels[$member->role->value],
                        'icon'  => $member->role->icon(),
                        'color' => $member->role->color(),
                    ],
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
    public function compliance(ProductDevelopment $productDevelopment, bool $canSeeCosts = true): array
    {
        $this->canSeeCosts = $canSeeCosts;
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

        return collect($checklistItems)
            ->reject(fn (ProductDevelopmentChecklistItem $checklistItem) => !$this->canSeeCosts && (bool) ProductDevelopmentTargetFieldEnum::tryFrom((string) $checklistItem->target_field)?->isCost())
            ->map(fn (ProductDevelopmentChecklistItem $checklistItem) => [
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
