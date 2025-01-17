<?php
/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-10h-02m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
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
    use WithNoStrictRules;

    protected WebUser $webUser;

    public function handle(WebUser $webUser, array $modelData): WebUser
    {
        if (Arr::exists($modelData, 'password')) {
            data_set($modelData, 'password', Hash::make($modelData['password']));
            data_set($modelData, 'auth_type', WebUserAuthTypeEnum::DEFAULT);
            data_set($modelData, 'data.legacy_password', null);
        }

        $webUser = $this->update($webUser, $modelData, ['data', 'settings']);

        if (Arr::hasAny($webUser->getChanges(), ['status'])) {
            CustomerHydrateWebUsers::dispatch($webUser->customer)->delay($this->hydratorsDelay);
        }

        return $webUser;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        $rules = [
            'username'   => [
                'sometimes',
                'required',
                $this->strict ? new AlphaDashDot() : 'string',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->webUser->website->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->webUser->id, 'operator' => '!='],
                    ]
                ),
            ],
            'email'      => [
                'sometimes',
                'nullable',
                $this->strict ? 'email' : 'string:500',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->webUser->website->id],
                        ['column' => 'deleted_at', 'operator' => 'notNull'],
                        ['column' => 'id', 'value' => $this->webUser->id, 'operator' => '!='],
                    ]
                ),

            ],
            'contact_name' => ['sometimes'],
            'data'       => ['sometimes', 'array'],
            'password'   => ['sometimes', 'required', app()->isLocal() || app()->environment('testing') || !$this->strict ? Password::min(3) : Password::min(8)->uncompromised()],
            'is_root'    => ['sometimes', 'boolean']
        ];

        if (!$this->strict) {

            $rules                    = $this->noStrictUpdateRules($rules);

        }

        return $rules;
    }

    public function AsController(WebUser $webUser, ActionRequest $request): WebUser
    {
        $this->webUser = $webUser;
        $this->initialisation($request);

        return $this->handle($webUser, $this->validatedData);
    }
}
