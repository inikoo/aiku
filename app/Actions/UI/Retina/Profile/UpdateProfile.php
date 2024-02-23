<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Profile;

use App\Actions\Traits\WithActionUpdate;
use App\Actions\UI\Retina\SysAdmin\UI\SetWebUserAvatarFromImage;
use App\Models\CRM\WebUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateProfile
{
    use WithActionUpdate;

    private bool $asAction = false;


    public function handle(WebUser $webUser, array $modelData, ?UploadedFile $avatar): WebUser
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
            SetWebUserAvatarFromImage::run(
                webUser: $webUser,
                imagePath: $avatar->getPathName(),
                originalFilename: $avatar->getClientOriginalName(),
                extension: $avatar->getClientOriginalExtension()
            );
        }

        $webUser->refresh();

        return $this->update($webUser, $modelData);
    }


    public function rules(): array
    {
        return [
            'password'    => ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'email'       => 'sometimes|required|email|unique:App\Models\CRM\WebUser,email',
            'about'       => 'sometimes|nullable|string|max:255',
            'language_id' => ['sometimes', 'required', 'exists:languages,id'],
            'app_theme'   => ['sometimes', 'required'],
            'avatar'      => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ],


        ];
    }


    public function asController(ActionRequest $request): WebUser
    {
        $this->fillFromRequest($request);

        $validated = $this->validateAttributes();
        return $this->handle($request->user(), Arr::except($validated, 'avatar'), Arr::get($validated, 'avatar'));
    }


}
