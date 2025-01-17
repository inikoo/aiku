<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\GrpAction;
use App\Actions\Traits\UI\WithProfile;
use App\Actions\Traits\WithActionUpdate;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateProfile extends GrpAction
{
    use WithActionUpdate;
    use WithProfile;

    public function handle(User $user, array $modelData): User
    {
        $user = $this->processProfileAvatar($modelData, $user);

        if (Arr::exists($modelData, 'app_theme')) {
            $appTheme = Arr::pull($modelData, 'app_theme');
            $modelData['settings']['app_theme'] = $appTheme;
        }

        return $this->update($user, $modelData, ['settings']);
    }


    public function rules(): array
    {
        return [
            'password'    => ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'       => 'sometimes|required|email|unique:App\Models\SysAdmin\User,email,'.request()->user()->id,
            'about'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'language_id' => ['sometimes', 'required', 'exists:languages,id'],
            'app_theme'   => ['sometimes', 'required'],
            'image'       => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ]
        ];
    }


    public function asController(ActionRequest $request): User
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($request->user(), $this->validatedData);
    }


}
