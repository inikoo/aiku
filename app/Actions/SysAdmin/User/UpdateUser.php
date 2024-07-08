<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateUsers;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\SysAdmin\User\UserAuthTypeEnum;
use App\Http\Resources\SysAdmin\UsersResource;
use App\Models\SysAdmin\User;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateUser extends GrpAction
{
    use WithActionUpdate;


    private User $user;

    public function handle(User $user, array $modelData): User
    {



        if(Arr::exists($modelData, 'password')) {
            $this->set('auth_type', UserAuthTypeEnum::DEFAULT);
        }


        $user= $this->update($user, $modelData, ['profile', 'settings']);

        if($user->wasChanged('status')) {
            GroupHydrateUsers::run($user->group);
        }

        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return  $request->user()->hasPermissionTo('sysadmin.edit');

    }

    public function rules(): array
    {
        return [
            'username'        => ['sometimes','required', 'lowercase',new AlphaDashDot(),

                                   Rule::notIn(['export', 'create']),
                                  new IUnique(
                                      table: 'users',
                                      extraConditions: [

                                          [
                                              'column'   => 'id',
                                              'operator' => '!=',
                                              'value'    => $this->user->id
                                          ],
                                      ]
                                  ),




            ],
            'password'        => ['sometimes','required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'legacy_password' => ['sometimes', 'string'],
            'email'           => ['sometimes', 'nullable', 'email',
                                  new IUnique(
                                      table: 'employees',
                                      extraConditions: [
                                          [
                                              'column' => 'group_id',
                                              'value'  => $this->group->id
                                          ],
                                          [
                                              'column'   => 'id',
                                              'operator' => '!=',
                                              'value'    => $this->user->id
                                          ],
                                      ]
                                  ),
                ],
            'contact_name'    => ['sometimes', 'string', 'max:255'],
            'reset_password'  => ['sometimes', 'boolean'],
            'auth_type'       => ['sometimes', Rule::enum(UserAuthTypeEnum::class)],
            'status'          => ['sometimes', 'boolean'],
            'language_id'     => ['sometimes', 'required', 'exists:languages,id'],
        ];
    }

    public function asController(User $user, ActionRequest $request): User
    {
        $this->user=$user;
        $this->initialisation($user->group, $request);
        return $this->handle($user, $this->validatedData);
    }

    public function action(User $user, $modelData): User
    {
        $this->user     =$user;
        $this->asAction = true;
        $this->initialisation($user->group, $modelData);

        return $this->handle($user, $this->validatedData);

    }

    public function jsonResponse(User $user): UsersResource
    {
        return new UsersResource($user);
    }
}
