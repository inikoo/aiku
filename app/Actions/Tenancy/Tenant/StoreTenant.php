<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Actions\Tenancy\Group\Hydrators\GroupHydrateTenants;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTenant
{
    use AsAction;
    use WithAttributes;

    public function handle(?Group $group, array $modelData): Tenant
    {
        if (!$group) {
            $group = StoreGroup::make()->asAction(
                [
                    'code'        => $modelData['code'],
                    'name'        => $modelData['name'],
                    'currency_id' => $modelData['currency_id']
                ]
            );
        }

        $modelData['ulid'] = Str::ulid();
        /** @var Tenant $tenant */

        data_set($modelData, 'settings.ui.name', Arr::get($modelData, 'name'));
        $tenant = $group->tenants()->create($modelData);


        $tenant->refresh();

        if (!$group->owner_id) {
            $group->update(
                [
                    'owner_id' => $tenant->id
                ]
            );
        }

        GroupHydrateTenants::dispatch($group);
        SetCurrencyHistoricFields::run($tenant->currency, $tenant->created_at);

        DB::statement("CREATE SCHEMA ".$tenant->schema());
        $tenant->execute(
            function (Tenant $tenant) {
                SetTenantLogo::run($tenant);
                $tenant->stats()->create();
                $tenant->procurementStats()->create();
                $tenant->inventoryStats()->create();
                $tenant->productionStats()->create();
                $tenant->marketingStats()->create();
                $tenant->salesStats()->create();
                $tenant->fulfilmentStats()->create();
                $tenant->accountingStats()->create();
                $tenant->mailStats()->create();
                $tenant->crmStats()->create();
                $tenant->webStats()->create();

                Artisan::call('tenants:artisan "migrate:fresh  --force --path=database/migrations/tenant --database=tenant" --tenant='.$tenant->slug);
                Artisan::call('tenants:artisan "db:seed --force --class=TenantsSeeder" --tenant='.$tenant->slug);

                CreateTenantStorageLink::run();
                StorePaymentServiceProvider::run(
                    modelData: [
                        'type' => 'account',
                        'data' => [
                            'service-code' => 'accounts'
                        ],
                        'code' => 'accounts'
                    ]
                );

                foreach (MailroomCodeEnum::cases() as $case) {
                    StoreMailroom::run(
                        [
                            'code' => $case->value
                        ]
                    );
                }
            }
        );


        return $tenant;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:tenants', 'between:2,6', 'alpha'],
            'name'        => ['required', 'max:64'],
            'email'       => ['required', 'email', 'unique:tenants'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'country_id'  => ['required', 'exists:countries,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'timezone_id' => ['required', 'exists:timezones,id'],
            'source'      => ['sometimes', 'array']
        ];
    }


    public function action(Group $group, $modelData): Tenant
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function getCommandSignature(): string
    {
        return 'create:tenant {code}  {email} {name} {country_code} {currency_code} {--l|language_code= : Language code} {--tz|timezone= : Timezone}
        {--g|group_slug= : group slug}
        {--s|source= : source for migration from other system}

        ';
    }

    public function asCommand(Command $command): int
    {
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


        $group = null;
        if ($command->option('group_slug')) {
            try {
                $group = Group::where('slug', $command->option('group_slug'))->firstOrFail();
            } catch (Exception $e) {
                $command->error($e->getMessage());

                return 1;
            }
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

        $tenant = $this->handle($group, $validatedData);

        $command->info("Tenant $tenant->slug created successfully 🎉");

        return 0;
    }


}
