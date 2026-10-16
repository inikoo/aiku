<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-25m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\TradeUnit\TradeUnitMarketEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentMember;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductDevelopmentTabData
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function brief(ProductDevelopment $productDevelopment, ?User $viewer = null): array
    {
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
        ];
    }
}
