<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\User;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class UpdateUserStatus
{
    use WithActionUpdate;

    private bool $asAction = false;


    public function handle(User $user, array $modelData): User
    {


        return $this->update($user, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return  $request->user()->can('sysadmin.edit');

    }


    public function rules(): array
    {
        return [
            'status' => ['required', 'boolean']
        ];
    }


    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($request->exists('username') and $request->get('username') != strtolower($request->get('username'))) {
            $validator->errors()->add('invalid_username', 'Username must be lowercase.');
        }
    }



    public function asController(User $user, ActionRequest $request): User
    {
        return $this->handle($user, $request->validated());
    }


    public function action(User $user, bool $status): User
    {
        $this->asAction = true;
        $this->setRawAttributes([
            'status'=> $status
        ]);
        $validatedData = $this->validateAttributes();

        return $this->handle($user, $validatedData);

    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
