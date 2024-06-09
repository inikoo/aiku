<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Profile;

use App\Actions\GrpAction;
use App\Actions\Helpers\Media\SaveModelImage;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\WebUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateProfile extends GrpAction
{
    use WithActionUpdate;


    public function handle(WebUser $webUser, array $modelData): WebUser
    {

        if (Arr::has($modelData, 'image')) {
            /** @var UploadedFile $image */
            $image = Arr::get($modelData, 'image');
            data_forget($modelData, 'image');
            $imageData = [
                'path'         => $image->getPathName(),
                'originalName' => $image->getClientOriginalName(),
                'extension'    => $image->getClientOriginalExtension(),
            ];
            $webUser      = SaveModelImage::run(
                model: $webUser,
                imageData: $imageData,
                scope: 'avatar'
            );
        }


        foreach ($modelData as $key => $value) {
            data_set(
                $modelData,
                match ($key) {
                    'app_theme' => 'settings.app_theme',
                    default     => $key
                },
                $value
            );
        };

        data_forget($modelData, 'app_theme');



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
            'image'       => [
                'sometimes',
                'nullable',
                File::image()
                    ->max(12 * 1024)
            ],


        ];
    }


    public function asController(ActionRequest $request): WebUser
    {
        $webUser = $request->user();
        $this->initialisation(app('group'), $request);


        return $this->handle($webUser, $this->validatedData, );
    }


}
