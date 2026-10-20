<?php

/*
 * Author Louis Perez
 * Created on 20-10-2026-19h-50m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Json;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

/**
 * When someone is picked as a member, what their permissions will stop them doing, so the person adding
 * them can ask for a role change. Membership alone already lets them open the development and do their part.
 */
class CheckProductDevelopmentMemberAccess extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    /**
     * @return array{warnings: array<int, string>}
     */
    public function handle(User $user, ?ProductDevelopmentMemberRoleEnum $role): array
    {
        $warnings = [];

        if (!$this->productDevelopmentAccess()->isStaff($user)) {
            $warnings[] = __('Can open this development and do their part, but not the trade unit, supplier product or purchase order pages it links to. Give them view access to Goods if they need those.');
        }

        if ($role === ProductDevelopmentMemberRoleEnum::PURCHASING) {
            $permissions = Organisation::where('group_id', $user->group_id)->pluck('id')->map(fn (int $id) => "procurement.$id.edit")->all();
            if ($permissions === [] || !$user->authTo($permissions)) {
                $warnings[] = __('Cannot raise purchase orders from the development: they need edit access to Procurement in an organisation.');
            }
        }

        return ['warnings' => $warnings];
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('group_id', $this->group->id)],
            'role'    => ['sometimes', 'nullable', Rule::enum(ProductDevelopmentMemberRoleEnum::class)],
        ];
    }

    /**
     * @return array{warnings: array<int, string>}
     */
    public function asController(ActionRequest $request): array
    {
        $this->initialisationFromGroup($request->user()->group, $request);

        return $this->handle(
            User::findOrFail($this->validatedData['user_id']),
            ProductDevelopmentMemberRoleEnum::tryFrom((string) ($this->validatedData['role'] ?? ''))
        );
    }
}
