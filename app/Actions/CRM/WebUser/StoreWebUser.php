<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\OrgAction;
use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\WebUser;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class StoreWebUser extends OrgAction
{
    private bool $asAction     = false;
    public int $hydratorsDelay = 0;
    private bool $strict       = true;


    public function handle(Customer $customer, array $modelData): Webuser
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->organisation_id);

        if (!$customer->shop->website) {
            abort(422, 'Website not set up');
        }
        if (Arr::exists($modelData, 'password')) {
            $modelData['password'] = Hash::make($modelData['password']);
        }
        /** @var \App\Models\SysAdmin\WebUser $webUser */
        $webUser = $customer->webUsers()->create(
            array_merge(
                $modelData,
                [
                    'website_id' => $customer->shop->website->id
                ]
            )
        );
        CustomerHydrateWebUsers::dispatch($customer);

        return $webUser;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->slug}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'type'     => ['sometimes', Rule::enum(WebUserTypeEnum::class)],
            'auth_type'=> ['sometimes', Rule::enum(WebUserAuthTypeEnum::class)],
            'username' => [
                'required',
                'string',
                'max:255',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->shop->website->id],
                        ['column' => 'deleted_at', 'value' => null],
                    ]
                ),
            ],
            'email'    => [
                'nullable',
                'max:255',
                new IUnique(
                    table: 'web_users',
                    extraConditions: [
                        ['column' => 'website_id', 'value' => $this->shop->website->id],
                        ['column' => 'deleted_at', 'value' => null],
                    ]
                ),

            ],

            'source_id' => [
                'sometimes',
                'nullable',
                'string',

            ],
            'data'       => ['sometimes', 'array'],
            'created_at' => ['sometimes', 'date'],
            'deleted_at' => ['sometimes', 'nullable', 'date'],
            'password'   =>
                [
                    'sometimes',
                    'required',
                    app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()
                ],

        ];

        if ($this->strict) {
            $strictRules = [
                'email' => [
                    'nullable',
                    'email',
                    new IUnique(
                        table: 'web_users',
                        extraConditions: [
                            ['column' => 'website_id', 'value' => $this->shop->website->id],
                            ['column' => 'deleted_at', 'value' => null],
                        ]
                    ),
                ],
            ];
            $rules       = array_merge($rules, $strictRules);
        }

        return $rules;
    }

    public function asController(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): Webuser
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customer, $this->validatedData);
    }

    public function action(Customer $customer, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Webuser
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $this->validatedData);
    }

}
