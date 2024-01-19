<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:24:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateUsers;
use App\Actions\SysAdmin\User\Hydrators\UserHydrateUniversalSearch;
use App\Actions\SysAdmin\User\UI\SetUserAvatar;
use App\Models\HumanResources\Employee;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use App\Rules\AlphaDashDot;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreUser
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;


    public function handle(Guest|Employee|Supplier|Agent $parent, array $modelData = []): User
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'contact_name', $parent->contact_name);

        $type = match (class_basename($parent)) {
            'Guest', 'Employee', 'Supplier', 'Agent' => strtolower(class_basename($parent)),
            default => null
        };

        data_set($modelData, 'type', $type);

        /** @var User $user */
        $user = $parent->user()->create($modelData);

        $user->stats()->create();
        $user->refresh();
        SetUserAvatar::dispatch(user: $user, saveHistory: false);
        UserHydrateUniversalSearch::dispatch($user);
        GroupHydrateUsers::dispatch($user->group);


        return $user;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function rules(): array
    {
        return [
            'username' => ['required', new AlphaDashDot(), 'unique:users,username', Rule::notIn(['export', 'create'])],
            'password' => ['required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'    => ['sometimes', 'nullable', 'email', 'unique:users,email']
        ];
    }


    public function action(Guest|Employee $parent, array $modelData = []): User
    {
        $this->asAction = true;

        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }


}
