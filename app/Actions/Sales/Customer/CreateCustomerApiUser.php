<?php
/** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 27 Oct 2022 11:58:25 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;


use App\Actions\Web\WebUser\StoreWebUser;
use App\Models\Central\Tenant;
use App\Models\Sales\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCustomerApiUser
{
    use AsAction;

    public string $commandSignature = 'customer:api-user {tenant_code} {customer_id}';
    public string $commandDescription = 'Create an user with api token to the given customer.';

    public function handle(Customer $customer, $tokenData): string
    {
        $webUser = StoreWebUser::run($customer);


        return $webUser->createToken(
            Arr::get($tokenData, 'name', 'full-access'),
            Arr::get($tokenData, 'abilities', ['*']),
        )->plainTextToken;
    }

    public function asCommand(Command $command): int
    {
        $tenant = Tenant::where('code', ($command->argument('tenant_code')))->firstOrFail();

        return $tenant->run(function () use ($command) {
            if ($customer = Customer::find($command->argument('customer_id'))) {
                if (!$customer->shop->website) {
                    $command->error("Shop {$customer->shop->name} do not have website");

                    return 1;
                }

                $token = $this->handle($customer, []);
                $command->line("Customer access token: $token");

                return 0;
            } else {
                $command->error("Customer not found: {$command->argument('customer_id')}");

                return 1;
            }
        });
    }


}
