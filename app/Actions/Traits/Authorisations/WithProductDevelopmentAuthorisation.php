<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Traits\Authorisations;

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\ActionRequest;

trait WithProductDevelopmentAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $user          = $request->user();
        $this->canEdit = $user->authTo('goods.edit');

        $productDevelopment = $request->route('productDevelopment');
        if ($productDevelopment instanceof ProductDevelopment) {
            return $this->canViewProductDevelopment($user, $productDevelopment);
        }

        return $this->isProductDevelopmentStaff($user) || $this->isProductDevelopmentMember($user);
    }

    public function isProductDevelopmentStaff(User $user): bool
    {
        return $user->authTo(['goods.view', 'masters.view', 'compliance.view']);
    }

    public function isProductDevelopmentMember(User $user, ?ProductDevelopment $productDevelopment = null): bool
    {
        if ($productDevelopment) {
            return $productDevelopment->members->contains('user_id', $user->id);
        }

        return ProductDevelopment::where('group_id', $user->group_id)
            ->whereHas('members', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }

    public function canViewProductDevelopment(User $user, ProductDevelopment $productDevelopment): bool
    {
        if ($productDevelopment->group_id !== $user->group_id) {
            return false;
        }

        return $this->isProductDevelopmentStaff($user) || $this->isProductDevelopmentMember($user, $productDevelopment);
    }

    public function canCreateProductDevelopment(User $user): bool
    {
        return $user->authTo('goods.edit');
    }

    /**
     * @return array<int, ProductDevelopmentMemberRoleEnum>
     */
    public function memberRoles(User $user, ProductDevelopment $productDevelopment): array
    {
        return $productDevelopment->members
            ->where('user_id', $user->id)
            ->pluck('role')
            ->all();
    }

    public function canMoveProductDevelopmentStage(User $user, ProductDevelopment $productDevelopment): bool
    {
        if ($productDevelopment->owner_id === $user->id) {
            return true;
        }

        $roles = $this->memberRoles($user, $productDevelopment);
        if (in_array(ProductDevelopmentMemberRoleEnum::PURCHASING, $roles, true)) {
            return true;
        }

        $currentStageRoles = $productDevelopment->stages
            ->where('position', $productDevelopment->current_position)
            ->pluck('role')
            ->all();

        return count(array_intersect(array_map(fn ($role) => $role->value, $roles), array_map(fn ($role) => $role->value, $currentStageRoles))) > 0;
    }
}
