<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\UI\Profile;

use App\Actions\RetinaAction;
use App\Actions\Traits\UI\WithProfile;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\WebUser;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaProfile extends RetinaAction
{
    use WithActionUpdate;
    use WithProfile;

    public function handle(WebUser $webUser, array $modelData): WebUser
    {

        if (Arr::exists($modelData, 'password')) {
            data_set($modelData, 'password', Hash::make($modelData['password']));
        }
        $webUser= $this->processProfileAvatar($modelData, $webUser);
        return $this->update($webUser, $modelData);
    }


    public function rules(): array
    {
        return [
            'password'    => ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? Password::min(4) : Password::min(8)->uncompromised()],
            'email'       => ['sometimes', 'required', 'email', new IUnique(
                table: 'web_users',
                extraConditions: [
                    ['column' => 'website_id', 'value' => $this->website->id],
                    ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ['column' => 'id', 'value' => $this->webUser->id, 'operator' => '!='],
                ]
            )],
            'username'       => ['sometimes', 'required', 'min:4' , new IUnique(
                table: 'web_users',
                extraConditions: [
                    ['column' => 'website_id', 'value' => $this->website->id],
                    ['column' => 'deleted_at', 'operator' => 'notNull'],
                    ['column' => 'id', 'value' => $this->webUser->id, 'operator' => '!='],
                ]
            )],
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

        $this->initialisation($request);
        return $this->handle($this->webUser, $this->validatedData);
    }

    public function action(WebUser $webUser, array $modelData): WebUser
    {
        $this->website= $webUser->website;
        $this->webUser = $webUser;
        $this->setRawAttributes($modelData);
        return $this->handle($webUser, $this->validateAttributes());
    }
}
