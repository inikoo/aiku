<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\Auth\GroupUser\UpdateGroupUser;
use App\Actions\WithActionUpdate;
use App\Enums\Auth\User\SynchronisableUserFields;
use App\Models\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateProfile
{
    use WithActionUpdate;

    private bool $asAction = false;

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     */
    public function handle(User $user, array $modelData, ?UploadedFile $avatar): User
    {


        UpdateGroupUser::run(
            $user->groupUser,
            Arr::only($modelData, SynchronisableUserFields::values())
        );

        if ($avatar) {
            $user->groupUser->addMedia($avatar)
                ->preservingOriginal()
                ->usingFileName(Str::orderedUuid().'.'.$avatar->extension())
                ->toMediaCollection('profile', 'group');
        }



        $user->refresh();



        return $this->update($user, Arr::except($modelData, SynchronisableUserFields::values()), ['profile', 'settings']);
    }


    public function rules(): array
    {
        return [
            'password'    => ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'       => 'sometimes|required|email|unique:App\Models\Auth\GroupUser,email',
            'about'       => 'sometimes|nullable|string',
            'language_id' => ['sometimes', 'required', 'exists:central.languages,id'],
            'avatar'      => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ],


        ];
    }


    public function asController(ActionRequest $request): User
    {
        $this->fillFromRequest($request);

        $validated = $this->validateAttributes();



        return $this->handle($request->user(), Arr::except($validated, 'avatar'), Arr::get($validated, 'avatar'));
    }



    public function htmlResponse(User $user): RedirectResponse
    {
        return Redirect::route('profile.show');
    }
}
