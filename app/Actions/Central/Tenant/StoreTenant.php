<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Mail\Mailroom\StoreMailroom;
use App\Enums\Mail\Mailroom\MailroomCodeEnum;
use App\Models\Central\Group;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTenant
{
    use AsAction;
    use WithAttributes;

    public function handle(Group $group, array $modelData): Tenant
    {


        $validatedData = $this->validateAttributes();

        $validatedData['ulid'] = Str::ulid();
        $tenant = $group->tenants()->create($validatedData);

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

        SetCurrencyHistoricFields::run($tenant->currency, $tenant->created_at);


        DB::statement("CREATE SCHEMA aiku_$tenant->code");
        $tenant->execute(
            function (Tenant $tenant) {
               Artisan::call('tenants:artisan "migrate:fresh --force --path=database/migrations/tenant --database=tenant" --tenant='.$tenant->code);
              //  Artisan::call('tenants:artisan "db:seed --force --class=TenantsSeeder" --tenant='.$tenant->code);


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

    public function rules()
    {
        return [
            'code' => ['sometimes', 'required', 'unique:groups', 'between:2,6', 'alpha'],
            'name' => ['sometimes', 'required', 'max:64'],
            'currency_id' => ['sometimes', 'required', 'exists:currencies,id'],
            'country_id'  => ['sometimes', 'required', 'exists:countries,id'],
            'language_id' => ['sometimes', 'required', 'exists:languages,id'],
            'timezone_id' => ['sometimes', 'required', 'exists:timezones,id'],
        ];
    }


    public function action(Group $group,$objectData): Tenant
    {

        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group,$validatedData);
    }
}
