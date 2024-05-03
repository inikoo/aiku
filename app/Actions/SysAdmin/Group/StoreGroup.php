<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:14:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateJobPositions;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Assets\Country;
use App\Models\Assets\Currency;
use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\SysAdmin\Group;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGroup
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData): Group
    {
        data_set($modelData, 'ulid', Str::ulid());

        /** @var Group $group */
        $group = Group::create($modelData);
        app()->instance('group', $group);
        SeedGroupPermissions::run($group);
        SeedGroupPaymentServiceProviders::run($group);

        $group->supplyChainStats()->create();
        $group->sysadminStats()->create();
        $group->humanResourcesStats()->create();
        $group->inventoryStats()->create();
        $group->crmStats()->create();
        $group->accountingStats()->create();
        $group->marketStats()->create();
        $group->fulfilmentStats()->create();
        $group->salesStats()->create();
        $group->salesIntervals()->create();

        SetGroupLogo::dispatch($group);


        foreach (MailroomCodeEnum::cases() as $case) {
            StoreMailroom::run(
                $group,
                [
                    'name' => $case->label(),
                    'code' => $case
                ]
            );
        }

        GroupHydrateJobPositions::run($group);

        return $group;
    }

    public function rules(): array
    {
        return [
            'code'        => ['required', 'required', 'unique:groups', 'between:2,6'],
            'name'        => ['required', 'required', 'max:64'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'country_id'  => ['required', 'exists:countries,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'timezone_id' => ['required', 'exists:timezones,id'],
            'subdomain'   => ['sometimes', 'nullable', 'unique:groups', 'between:2,64'],
        ];
    }


    public function action($modelData): Group
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    public string $commandSignature = 'group:create {code} {name} {country_code} {currency_code} {--s|subdomain=} {--l|language_code=} {--tz|timezone= : Timezone}';

    public function asCommand(Command $command): int
    {

        if($command->option('language_code')) {
            try {
                $language = Language::where('code', $command->option('language_code'))->firstOrFail();
            } catch (Exception $e) {
                $command->error($e->getMessage());

                return 1;
            }
        } else {
            $language = Language::where('code', 'en')->firstOrFail();
        }

        try {
            $currency = Currency::where('code', $command->argument('currency_code'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        try {
            $country = Country::where('code', $command->argument('country_code'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
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

        $this->setRawAttributes([
            'code'        => $command->argument('code'),
            'name'        => $command->argument('name'),
            'country_id'  => $country->id,
            'currency_id' => $currency->id,
            'language_id' => $language->id,
            'timezone_id' => $timezone->id,
            'subdomain'   => $command->option('subdomain') ?? null,
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->handle($validatedData);

        $command->info('Done!');

        return 0;
    }
}
