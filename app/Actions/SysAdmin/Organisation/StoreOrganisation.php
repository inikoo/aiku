<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProvider;
use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Procurement\OrgPartner\StoreOrgPartner;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Helpers\Address;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Rules\ValidAddress;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOrganisation
{
    use AsAction;
    use WithAttributes;

    public function handle(Group $group, array $modelData): Organisation
    {
        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');

        data_set($modelData, 'ulid', Str::ulid());
        data_set($modelData, 'settings.ui.name', Arr::get($modelData, 'name'));

        /** @var Organisation $organisation */
        $organisation = $group->organisations()->create($modelData);
        $organisation->refresh();
        SeedOrganisationPermissions::run($organisation);
        SeedOrganisationJobPositions::run($organisation);


        if ($addressData) {
            data_set($addressData, 'owner_type', 'Organisation');
            data_set($addressData, 'owner_id', $organisation->id);
            $address = Address::create($addressData);

            $organisation->address()->associate($address);

            $organisation->location = $organisation->address->getLocation();
            $organisation->save();
        }
        $superAdmins = $group->users()->with('roles')->get()->filter(
            fn ($user) => $user->roles->where('name', 'super-admin')->toArray()
        );

        foreach ($superAdmins as $superAdmin) {
            UserAddRoles::run($superAdmin, [
                Role::where(
                    'name',
                    RolesEnum::getRoleName(
                        match ($organisation->type) {
                            OrganisationTypeEnum::SHOP           => 'org-shop-admin',
                            OrganisationTypeEnum::DIGITAL_AGENCY => 'org-digital_agency-admin',
                            OrganisationTypeEnum::AGENT          => 'org-agent-admin',
                        },
                        $organisation
                    )
                )->where('scope_id', $organisation->id)->first()
            ]);
        }


        $organisation->refresh();

        SetCurrencyHistoricFields::run($organisation->currency, $organisation->created_at);

        $organisation->stats()->create();
        $organisation->humanResourcesStats()->create();
        $organisation->procurementStats()->create();
        $organisation->inventoryStats()->create();
        $organisation->accountingStats()->create();


        $organisation->webStats()->create();


        if ($organisation->type == OrganisationTypeEnum::SHOP) {
            $organisation->mailStats()->create();
            $organisation->crmStats()->create();
            $organisation->salesStats()->create();
            $organisation->salesIntervals()->create();
            $organisation->ordersIntervals()->create();
            $organisation->mailshotsIntervals()->create();
            $organisation->fulfilmentStats()->create();
            $organisation->productionStats()->create();
            $organisation->marketStats()->create();
            $organisation->manufactureStats()->create();

            $paymentServiceProvider = PaymentServiceProvider::where('type', PaymentServiceProviderTypeEnum::ACCOUNT)->first();

            StoreOrgPaymentServiceProvider::make()->action(
                $paymentServiceProvider,
                organisation: $organisation,
                modelData: [
                    'code' => 'account-'.$organisation->code,
                ]
            );

            $otherOrganisations = Organisation::where('type', OrganisationTypeEnum::SHOP)->where('group_id', $group->id)->where('id', '!=', $organisation->id)->get();
            foreach ($otherOrganisations as $otherOrganisation) {
                StoreOrgPartner::make()->action($otherOrganisation, $organisation);
                StoreOrgPartner::make()->action($organisation, $otherOrganisation);
            }
        }

        return $organisation;
    }


    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:organisations', 'max:12', 'alpha'],
            'name'        => ['required', 'max:255'],
            'email'       => ['required', 'nullable', 'email', 'unique:organisations'],
            'phone'       => ['sometimes', 'nullable', 'phone:AUTO'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'country_id'  => ['required', 'exists:countries,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'timezone_id' => ['required', 'exists:timezones,id'],
            'source'      => ['sometimes', 'array'],
            'type'        => ['required', Rule::enum(OrganisationTypeEnum::class)],
            'address'     => ['sometimes', 'required', new ValidAddress()],
            'created_at'  => ['sometimes', 'date'],
        ];
    }

    public function asController(Group $group, ActionRequest $request): Organisation
    {
        $this->setRawAttributes($request->all());
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function htmlResponse(Organisation $organisation): RedirectResponse
    {
        return Redirect::route('grp.org.dashboard.show', $organisation->slug);
    }

    public function action(Group $group, $modelData): Organisation
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function getCommandSignature(): string
    {
        return 'org:create {group} {type} {code} {email} {name} {country_code} {currency_code} {--l|language_code= : Language code} {--tz|timezone= : Timezone}
        {--s|source= : source for migration from other system}';
    }

    public function asCommand(Command $command): int
    {
        try {
            $group = Group::where('code', $command->argument('group'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }
        setPermissionsTeamId($group->id);
        try {
            $country = Country::where('code', $command->argument('country_code'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        try {
            $currency = Currency::where('code', $command->argument('currency_code'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        if ($command->option('language_code')) {
            try {
                $language = Language::where('code', $command->option('language_code'))->firstOrFail();
            } catch (Exception $e) {
                $command->error($e->getMessage());

                return 1;
            }
        } else {
            $language = Language::where('code', 'en-gb')->firstOrFail();
        }

        if ($command->option('timezone')) {
            try {
                $timezone = Timezone::where('name', $command->option('timezone'))->firstOrFail();
            } catch (Exception $e) {
                $command->error($e->getMessage());

                return 1;
            }
        } else {
            $timezone = Timezone::where('name', 'UTC')->firstOrFail();
        }


        $source = [];
        if ($command->option('source')) {
            if (Str::isJson($command->option('source'))) {
                $source = json_decode($command->option('source'), true);
            } else {
                $command->error('Source data is not a valid json');

                return 1;
            }
        }

        $this->setRawAttributes([
            'type'        => $command->argument('type'),
            'code'        => $command->argument('code'),
            'name'        => $command->argument('name'),
            'email'       => $command->argument('email'),
            'country_id'  => $country->id,
            'currency_id' => $currency->id,
            'language_id' => $language->id,
            'timezone_id' => $timezone->id,
            'source'      => $source
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $organisation = $this->handle($group, $validatedData);

        $command->info("Organisation $organisation->slug created successfully 🎉");

        return 0;
    }


}
