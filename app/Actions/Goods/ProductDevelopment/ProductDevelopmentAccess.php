<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-10h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentMember;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductDevelopmentAccess
{
    /**
     * @var array<int, array<int, int>>
     */
    private array $agentIdsByUser = [];

    public function isStaff(User $user): bool
    {
        return $user->authTo(['goods.view', 'masters.view', 'compliance.view']);
    }

    /**
     * @return array<int, int>
     */
    public function agentIds(User $user): array
    {
        return $this->agentIdsByUser[$user->id] ??= Agent::query()
            ->where('group_id', $user->group_id)
            ->whereIn('organisation_id', $user->authorisedAgentsOrganisations()->pluck('organisations.id'))
            ->pluck('id')
            ->all();
    }

    public function isMember(User $user, ProductDevelopment $productDevelopment): bool
    {
        return $productDevelopment->members()->where('user_id', $user->id)->exists();
    }

    public function isAgentOf(User $user, ProductDevelopment $productDevelopment): bool
    {
        $agentIds = $this->agentIds($user);
        if ($agentIds === []) {
            return false;
        }

        return in_array($productDevelopment->agent_id, $agentIds, true)
            || in_array($productDevelopment->supplier?->agent_id, $agentIds, true);
    }

    public function canView(User $user, ProductDevelopment $productDevelopment): bool
    {
        if ($productDevelopment->group_id !== $user->group_id) {
            return false;
        }

        return $this->isStaff($user) || $this->isAgentOf($user, $productDevelopment) || $this->isMember($user, $productDevelopment);
    }

    public function canSeeAny(User $user): bool
    {
        if ($this->isStaff($user)) {
            return true;
        }

        return $this->restrictToVisible(ProductDevelopment::query()->where('group_id', $user->group_id), $user)->exists();
    }

    public function canSeeCosts(User $user, ProductDevelopment $productDevelopment): bool
    {
        return $this->isStaff($user) || $this->isAgentOf($user, $productDevelopment);
    }

    /**
     * @return array<int, ProductDevelopmentMemberRoleEnum>
     */
    public function memberRoles(User $user, ProductDevelopment $productDevelopment): array
    {
        return $productDevelopment->members()
            ->where('user_id', $user->id)
            ->get()
            ->map(fn (ProductDevelopmentMember $member) => $member->role)
            ->all();
    }

    public function canEdit(User $user, ProductDevelopment $productDevelopment): bool
    {
        if ($productDevelopment->owner_id === $user->id) {
            return true;
        }

        return in_array(ProductDevelopmentMemberRoleEnum::PURCHASING, $this->memberRoles($user, $productDevelopment), true);
    }

    public function restrictToVisible(Builder $query, User $user): Builder
    {
        if ($this->isStaff($user)) {
            return $query;
        }

        $agentIds = $this->agentIds($user);

        return $query->where(function (Builder $query) use ($user, $agentIds) {
            $query->whereExists(fn ($subQuery) => $subQuery->select(DB::raw(1))
                ->from('product_development_members')
                ->whereColumn('product_development_members.product_development_id', 'product_developments.id')
                ->where('product_development_members.user_id', $user->id));

            if ($agentIds !== []) {
                $query->orWhereIn('product_developments.agent_id', $agentIds)
                    ->orWhereExists(fn ($subQuery) => $subQuery->select(DB::raw(1))
                        ->from('suppliers')
                        ->whereColumn('suppliers.id', 'product_developments.supplier_id')
                        ->whereIn('suppliers.agent_id', $agentIds));
            }
        });
    }
}
