<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:51:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Mailroom\Mailroom\StoreMailroom;
use App\Enums\Mailroom\Mailroom\MailroomCodeEnum;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTenant
{
    use AsAction;

    public function handle(array $modelData): Tenant
    {
        $tenant = Tenant::create($modelData);

        $tenant->stats()->create();
        $tenant->procurementStats()->create();
        $tenant->inventoryStats()->create();
        $tenant->productionStats()->create();
        $tenant->marketingStats()->create();
        $tenant->salesStats()->create();
        $tenant->fulfilmentStats()->create();
        $tenant->accountingStats()->create();
        $tenant->refresh();


        DB::statement("CREATE SCHEMA aiku_$tenant->slug");
        $tenant->execute(
            function (Tenant $tenant) {
                Artisan::call('tenants:artisan "migrate:fresh --force --path=database/migrations/tenant --database=tenant" --tenant='.$tenant->slug);
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
}
