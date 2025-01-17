<?php

/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-10h-02m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Models\CRM\WebUser;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaWebUser extends RetinaAction
{
    use WithActionUpdate;

    private WebUser $webUserToUpdate;

    public function handle(WebUser $webUser, array $modelData): WebUser
    {
        if (Arr::exists($modelData, 'password')) {
            data_set($modelData, 'password', Hash::make($modelData['password']));
            data_set($modelData, 'auth_type', WebUserAuthTypeEnum::DEFAULT);
            data_set($modelData, 'data.legacy_password', null);
        }

        $webUser = $this->update($webUser, $modelData, ['data']);

        if (Arr::hasAny($webUser->getChanges(), ['status'])) {
            CustomerHydrateWebUsers::dispatch($webUser->customer);
        }

        return $webUser;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $this->customer->id == $request->route()->parameter('webUser')->customer_id and $request->user()->is_root;
    }

    public function rules(): array
    {
        return [
            'username'     => [
                'sometimes',
                'required',
                new AlphaDashDot(),
                'min:4',
                'max:255',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->webUser->website->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->webUserToUpdate->id, 'operator' => '!='],
                    ]
                ),
            ],
            'email'        => [
                'sometimes',
                'required',
                'email',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->webUser->website->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->webUserToUpdate->id, 'operator' => '!='],
                    ]
                ),
            ],
            'contact_name' => ['sometimes', 'string', 'max:255'],
            'password'     => ['sometimes', 'required', app()->isLocal() || app()->environment('testing') ? Password::min(3) : Password::min(8)->uncompromised()],
        ];
    }

    public function AsController(WebUser $webUser, ActionRequest $request): WebUser
    {
        $this->webUserToUpdate = $webUser;
        $this->initialisation($request);

        return $this->handle($webUser, $this->validatedData);
    }
}
