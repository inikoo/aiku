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
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property \App\Models\Auth\User $user
 */
class UpdateUserStatus
{
    use WithActionUpdate;

    private bool $asAction = false;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(User $user, array $modelData): User
    {
        $groupUser = $user->groupUser()->first();

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        if(!$groupUser->status) {
            throw ValidationException::withMessages(["You can't change your status"]);
        }

        return $this->update($user, $modelData);
    }

    public function authorize(User $user, ActionRequest $request): bool
    {
        if ($user->id == $request->user()) {
            return true;
        }

        return false;
    }


    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', 'boolean']
        ];
    }


    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($request->exists('username') and $request->get('username') != strtolower($request->get('username'))) {
            $validator->errors()->add('invalid_username', 'Username must be lowercase.');
        }
    }


    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function asController(User $user, ActionRequest $request): User
    {
        return $this->handle($user, $request->validated());
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(User $user, $objectData): User
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($user, $validatedData);

    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
