<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\SysAdmin\User\SetUserAvatarFromImage;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\SysAdmin\User\SynchronisableUserFieldsEnum;
use App\Models\SysAdmin\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateProfile
{
    use WithActionUpdate;

    private bool $asAction = false;


    public function handle(User $user, array $modelData, ?UploadedFile $avatar): User
    {
        foreach ($modelData as $key => $value) {
            data_set(
                $modelData,
                match ($key) {
                    'app_theme'     => 'settings.app_theme',
                    default         => $key
                },
                $value
            );
        };

        data_forget($modelData, 'app_theme');

        if ($avatar) {
            SetUserAvatarFromImage::run(
                user: $user,
                imagePath: $avatar->getPathName(),
                originalFilename: $avatar->getClientOriginalName(),
                extension: $avatar->getClientOriginalExtension()
            );
        }

        $user->refresh();

        return $this->update($user, Arr::except($modelData, SynchronisableUserFieldsEnum::values()), ['profile']);
    }


    public function rules(): array
    {
        return [
            'password'    => ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'       => 'sometimes|required|email|unique:App\Models\SysAdmin\User,email,' . request()->user()->id,
            'about'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'language_id' => ['sometimes', 'required', 'exists:languages,id'],
            'app_theme'   => ['sometimes', 'required'],
            'avatar'      => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ]
        ];
    }


    public function asController(ActionRequest $request): User
    {
        $this->fillFromRequest($request);

        $validated = $this->validateAttributes();
        return $this->handle($request->user(), Arr::except($validated, 'avatar'), Arr::get($validated, 'avatar'));
    }


}
