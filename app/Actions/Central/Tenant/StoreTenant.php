<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Central\Group\Hydrators\GroupHydrateTenants;
use App\Actions\Central\Group\StoreGroup;
use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Assets\Currency;
use App\Models\Central\Group;
use App\Models\Central\Tenant;
use Exception;
use Illuminate\Console\Command;
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
        $tenant = $group->tenants()->create($modelData);


        $tenant->stats()->create();
        $tenant->procurementStats()->create();
        $tenant->inventoryStats()->create();
        $tenant->productionStats()->create();
        $tenant->marketingStats()->create();
        $tenant->salesStats()->create();
        $tenant->fulfilmentStats()->create();
        $tenant->accountingStats()->create();
        $tenant->mailStats()->create();
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

        DB::statement("CREATE SCHEMA aiku_$tenant->code");
        $tenant->execute(
            function (Tenant $tenant) {
                Artisan::call('tenants:artisan "migrate:fresh  --force --path=database/migrations/tenant --database=tenant" --tenant='.$tenant->code);
                Artisan::call('tenants:artisan "db:seed --force --class=TenantsSeeder" --tenant='.$tenant->code);


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
            'code'        => ['required', 'unique:groups', 'between:2,6', 'alpha'],
            'name'        => ['required', 'max:64'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'country_id'  => ['required', 'exists:countries,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'timezone_id' => ['required', 'exists:timezones,id'],
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
        return 'create:tenant {user_id} {role} {--g|group_slug= : group slug}';
    }

    public function asCommand(Command $command): int
    {
        try {
            $currency = Currency::where('code', $command->argument('currency_code'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $group=null;
        if ($command->option('group_slug')) {
            try {
                $group = Group::where('slug', $command->argument('group_slug'))->firstOrFail();
            } catch (Exception $e) {
                $command->error($e->getMessage());
                return 1;
            }
        }

        $this->setRawAttributes([
            'code'        => $command->argument('code'),
            'name'        => $command->argument('name'),
            'currency_id' => $currency->id
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->handle($group, $validatedData);

        $command->info('Done!');

        return 0;
    }


}
