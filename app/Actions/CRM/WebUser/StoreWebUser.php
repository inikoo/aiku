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
use App\Models\CRM\WebUser;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class StoreWebUser extends OrgAction
{
    private Customer $customer;

    public function handle(Customer $customer, array $modelData): Webuser
    {
        data_set($modelData, 'language_id', $customer->shop->language_id, overwrite: false);
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);
        data_set($modelData, 'shop_id', $customer->shop_id);

        if (!$customer->shop->website) {
            abort(422, 'Website not set up');
        }
        if (Arr::exists($modelData, 'password')) {
            $modelData['password'] = Hash::make($modelData['password']);
        }
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
        SetWebUserAvatar::dispatch(userable: $webUser, saveHistory: false);
        CustomerHydrateWebUsers::dispatch($customer);

        return $webUser;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }

    public function rules(): array
    {
        $rules = [
            'type'      => ['sometimes', Rule::enum(WebUserTypeEnum::class)],
            'auth_type' => ['sometimes', Rule::enum(WebUserAuthTypeEnum::class)],
            'username'  => [
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


            'source_id'  => [
                'sometimes',
                'nullable',
                'string',

            ],
            'is_root'    => ['required', 'boolean'],
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

        $emailRule = [
            'email',
            'max:255',
            new IUnique(
                table: 'web_users',
                extraConditions: [
                    ['column' => 'website_id', 'value' => $this->shop->website->id],
                    ['column' => 'deleted_at', 'value' => null],
                ]
            ),

        ];

        if ($this->customer->hasUsers()) {
            $rules['email'] = array_merge(['sometimes', 'nullable'], $emailRule);
        } else {
            $rules['email'] = $emailRule;
        }


        return $rules;
    }

    public function asController(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): Webuser
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customer, $this->validatedData);
    }

    public function action(Customer $customer, array $modelData, int $hydratorsDelay = 0): Webuser
    {
        $this->asAction       = true;
        $this->customer       = $customer;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($customer->shop, $modelData);

        return $this->handle($customer, $this->validatedData);
    }

    public string $commandSignature = 'web-user:create {customer : customer slug} {username} {--e|email=} {--P|password=}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $customer = Customer::where('slug', $command->argument('customer'))->firstOrFail();
        } catch (Exception) {
            $command->error('Customer not found');

            return 1;
        }

        $this->customer = $customer;

        if ($command->option('password')) {
            $password = $command->option('password');
        } else {
            if (app()->isLocal() || app()->environment('testing')) {
                $password = 'hello';
            } else {
                $password = $command->secret('Enter the password');
            }
        }

        $data = [
            'username' => $command->argument('username'),
            'password' => $password,
            'email'    => $command->option('email'),
            'type'     => WebUserTypeEnum::WEB,
            'is_root'  => !$this->customer->hasUsers()
        ];


        $this->initialisationFromShop($customer->shop, $data);

        $webUser = $this->handle($customer, $this->validatedData);

        $command->line("Web user $webUser->username created successfully 🫡");


        return 0;
    }


}
