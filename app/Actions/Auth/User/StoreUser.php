<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Auth\GroupUser\Hydrators\GroupUserHydrateTenants;
use App\Actions\Central\User\Hydrators\UserHydrateUniversalSearch;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateUsers;
use App\Models\Auth\GroupUser;
use App\Models\Auth\Guest;
use App\Models\Auth\User;
use App\Models\HumanResources\Employee;
use App\Rules\AlphaDashDot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Validation\Validator;

class StoreUser
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    private ?GroupUser $groupUser;

    /**
     * @throws \Throwable
     */
    public function handle(Guest|Employee $parent, GroupUser $groupUser, array $objectData = []): User|ValidationException
    {
        $organisation = app('currentTenant');

        $user = DB::transaction(function () use ($groupUser, $organisation, $parent, $objectData) {
            /** @var \App\Models\Auth\User $user */

            $dataFromGroupUser = [
                'group_user_id'   => $groupUser->id,
                'username'        => $groupUser->username,
                'password'        => $groupUser->password,
                'legacy_password' => $groupUser->legacy_password,
                'contact_name'    => $parent->contact_name,
                'auth_type'       => $groupUser->auth_type,
                'about'           => $groupUser->about,
                'avatar_id'       => $groupUser->avatar_id,

            ];

            $type=match (class_basename($parent)) {
                'Guest','Employee','Supplier','Agent'=>strtolower(class_basename($parent)),
                default=> null
            };

            data_set($objectData, 'type', $type);

            $user = $parent->user()->create(
                array_merge($objectData, $dataFromGroupUser)
            );
            $groupUser->tenants()
                ->attach($organisation->id, [
                    'user_id' => $user->id,
                    'data'    => [
                        'contact_name' => $parent->contact_name,
                    ]
                ]);

            $user->stats()->create();

            return $user;
        });

        if ($groupUser->avatar) {
            $groupUser->avatar->tenants()->attach($organisation->id);
        }

        $groupUser->refresh();
        OrganisationHydrateUsers::dispatch($organisation);
        GroupUserHydrateTenants::dispatch($groupUser);
        UserHydrateUniversalSearch::dispatch($user);


        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        if ($this->groupUser) {
            return [];
        } else {
            return [
                'username' => ['required', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username', Rule::notIn(['export', 'create'])],
                'password' => ['required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
                'email'    => ['required', 'email', 'unique:App\Models\SysAdmin\SysUser,email']
            ];
        }
    }

    public function afterValidator(Validator $validator): void
    {
        if ($this->groupUser && $this->groupUser->tenants()->where('tenant_id', app('currentTenant')->id)->exists()) {
            $validator->errors()->add('tenant', 'Group-user only can add one user per tenant');
        }
    }


    /**
     * @throws \Throwable
     */
    public function action(Guest|Employee $parent, ?GroupUser $groupUser, array $objectData = []): User|ValidationException
    {
        $this->asAction  = true;
        $this->groupUser = $groupUser;
        $this->setRawAttributes($groupUser ? [] : $objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $groupUser, $validatedData);
    }


}
