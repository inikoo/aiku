<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\WithActionUpdate;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property User $user
 */
class UpdateProfile
{
    use WithActionUpdate;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     */
    public function handle(User $user, array $modelData, ?UploadedFile $avatar): User
    {
        if (Arr::has($modelData, 'password')) {
            $modelData['password'] = Hash::make($modelData['password']);
        }

        $user = $this->update($user, $modelData, ['profile', 'settings']);

        if ($avatar) {
            $user->addMedia($avatar)
                ->preservingOriginal()
                ->usingFileName(Str::orderedUuid().'.'.$avatar->extension())
                ->toMediaCollection('profile');
        }

        return $user;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'username' => ['sometimes', 'required', 'alpha_dash', Rule::unique('users', 'username')->ignore($request->user())],
            'about'    => 'sometimes|nullable|string',
            'email'    => 'sometimes|nullable|email',
            'password' => ['sometimes', 'required', Password::min(8)->uncompromised()],
            'language' => 'sometimes|required|exists:languages,code',
            'avatar'   => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ],
        ];
    }


    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function asController(ActionRequest $request): User
    {
        $validated = $request->validatedShiftToArray([
                                                         'language' => 'settings',
                                                     ]);


        return $this->handle($request->user(), Arr::except($validated, 'avatar'), Arr::get($validated, 'avatar'));
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('profile.show');
    }

    public function jsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }
}
