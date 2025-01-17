<?php
/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-09h-57m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreRetinaWebUser extends RetinaAction
{
    use WithNoStrictRules;

    protected Customer $customer;
    protected Customer|FulfilmentCustomer $parent;

    /**
     * @throws \Throwable
     */
    public function handle(Customer $customer, array $modelData): Webuser
    {
        data_set($modelData, 'language_id', $customer->shop->language_id, overwrite: false);
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);


        if (Arr::exists($modelData, 'password')) {
            $modelData['password'] = Hash::make($modelData['password']);
        }
        $webUser = DB::transaction(function () use ($customer, $modelData) {
            /** @var WebUser $webUser */
            $webUser = $customer->webUsers()->create(
                array_merge(
                    $modelData,
                    [
                        'website_id' => $customer->shop->website->id
                    ]
                )
            );
            $webUser->stats()->create();
            $webUser->refresh();

            return $webUser;
        });

        CustomerHydrateWebUsers::dispatch($webUser->customer)->delay($this->hydratorsDelay);

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
            'contact_name' => ['sometimes', 'nullable', 'max:255'],
            'type'      => ['sometimes', Rule::enum(WebUserTypeEnum::class)],
            'auth_type' => ['sometimes', Rule::enum(WebUserAuthTypeEnum::class)],
            'username'  => [
                'required',
                'string',
                'max:255',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->webUser->website->id]
                    ]
                ),
            ],
            'is_root'   => ['required', 'boolean'],
            'data'      => ['sometimes', 'array'],
            'password'  =>
                [
                    'sometimes',
                    'required',
                    app()->isLocal() || app()->environment('testing') || !$this->strict ? Password::min(3) : Password::min(8)->uncompromised()
                ],

        ];

        $emailRule = [
            $this->strict ? 'email' : 'string:500',
            'max:255',
            new IUnique(
                table: 'web_users',
                extraConditions: [
                    ['column' => 'website_id', 'value' => $this->webUser->website->id]
                ]
            ),
        ];

        if ($this->customer->hasUsers()) {
            $rules['email'] = array_merge(['sometimes', 'nullable'], $emailRule);
        } else {
            $rules['email'] = $emailRule;
        }

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    public function prepareForValidation(ActionRequest $request): void
    {

        if (!$this->webUser->shop->website) {
            abort(422, 'Website not set up');
        }

        if ($this->get('type') === null) {
            $this->fill(['type' => WebUserTypeEnum::WEB]);
        }

        if (!$this->customer->hasUsers()) {
            $this->fill(['is_root' => true]);
        } elseif ($this->get('is_root') == null) {
            $this->fill(['is_root' => false]);
        }
    }

    /**
     * @throws \Throwable
     */
    public function asController(ActionRequest $request): Webuser
    {
        $customer = $request->user()->customer;
        $this->parent   = $customer;
        $this->customer = $customer;
        $this->initialisation($request);

        return $this->handle($customer, $this->validatedData);
    }

    public function htmlResponse(WebUser $webUser): Response
    {
        return Inertia::location(route('retina.sysadmin.web-users.show', [
            'webUser'            => $webUser->slug
        ]));
    }

}
