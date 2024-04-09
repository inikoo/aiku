<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 12:29:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderEnum;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Group;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedGroupPaymentServiceProviders
{
    use AsAction;

    public function handle(Group $group): void
    {
        $paymentServiceProvidersData = collect(PaymentServiceProviderEnum::values());

        $paymentServiceProvidersData->each(function ($modelData) use ($group) {
            $paymentServiceProvider=PaymentServiceProvider::where('code', $modelData)->first();

            if(!$paymentServiceProvider) {
                StorePaymentServiceProvider::make()->action(
                    $group,
                    [
                    'code' => $modelData,
                    'type' => PaymentServiceProviderEnum::types()[$modelData],
                    'name' => PaymentServiceProviderEnum::labels()[$modelData]
                ]
                );
            }

        });
    }


    public string $commandSignature = 'groups:seed-payment-service-providers';

    public function asCommand(Command $command): int
    {
        foreach (Group::all() as $group) {
            $command->info("Seeding payment service providers for group: $group->name");
            setPermissionsTeamId($group->id);
            $this->handle($group);
        }

        return 0;
    }

}
