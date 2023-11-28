<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Elasticsearch\CreateElasticSearchOrganisationAlias;
use App\Actions\Organisation\Group\Hydrators\GroupHydrateOrganisations;
use App\Actions\Organisation\Group\StoreGroup;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Organisation\Group;
use App\Models\Organisation\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOrganisation
{
    use AsAction;
    use WithAttributes;

    public function handle(?Group $group, array $modelData): Organisation
    {
        if (!$group) {
            $group = StoreGroup::make()->action(
                [
                    'code'        => $modelData['code'],
                    'name'        => $modelData['name'],
                    'currency_id' => $modelData['currency_id']
                ]
            );
        }

        data_set($modelData, 'ulid', Str::ulid());


        data_set($modelData, 'settings.ui.name', Arr::get($modelData, 'name'));

        /** @var Organisation $organisation */
        $organisation = $group->organisations()->create($modelData);


        $organisation->refresh();

        if (!$group->owner_id) {
            $group->update(
                [
                    'owner_id' => $organisation->id
                ]
            );
        }

        GroupHydrateOrganisations::dispatch($group);
        SetCurrencyHistoricFields::run($organisation->currency, $organisation->created_at);


        CreateElasticSearchOrganisationAlias::run($organisation);
        SetOrganisationLogo::run($organisation);
        $organisation->stats()->create();
        $organisation->procurementStats()->create();
        $organisation->inventoryStats()->create();
        $organisation->productionStats()->create();
        $organisation->marketStats()->create();
        $organisation->salesStats()->create();
        $organisation->fulfilmentStats()->create();
        $organisation->accountingStats()->create();
        $organisation->mailStats()->create();
        $organisation->crmStats()->create();
        $organisation->webStats()->create();

        Artisan::call("db:seed --force --class=JobPositionSeeder");


        StorePaymentServiceProvider::run(
            modelData: [
                'type' => 'account',
                'data' => [
                    'service-code' => 'accounts'
                ],
                'code' => 'accounts'
            ]
        );




        return $organisation;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'unique:organisations', 'between:2,6', 'alpha'],
            'name'        => ['required', 'max:64'],
            'email'       => ['required', 'email', 'unique:organisations'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'country_id'  => ['required', 'exists:countries,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'timezone_id' => ['required', 'exists:timezones,id'],
            'source'      => ['sometimes', 'array']
        ];
    }


    public function action(Group $group, $modelData): Organisation
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function getCommandSignature(): string
    {
        return 'create:organisation {code}  {email} {name} {country_code} {currency_code} {--l|language_code= : Language code} {--tz|timezone= : Timezone}
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

        $organisation = $this->handle($group, $validatedData);

        $command->info("Organisation $organisation->slug created successfully 🎉");

        return 0;
    }


}
