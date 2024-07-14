<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

/** @noinspection PhpUnused */

namespace App\Actions\CRM\Customer;

use App\Actions\CRM\WebUser\HydrateWebUser;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Models\CRM\Customer;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCustomerApiUser
{
    use AsAction;

    public string $commandSignature   = 'customer:api-user {customer_slug}';
    public string $commandDescription = 'Create an user with api token to the given customer.';

    public function handle(Customer $customer, $tokenData): string
    {
        $webUser = StoreWebUser::run($customer);


        $token= $webUser->createToken(
            Arr::get($tokenData, 'name', 'full-access'),
            Arr::get($tokenData, 'abilities', ['*']),
        )->plainTextToken;
        HydrateWebUser::make()->tokens($webUser);
        return $token;
    }

    public function asCommand(Command $command): int
    {

        try {
            $customer=Customer::where('slug', $command->argument('customer_slug'))->firstOrFail();
        } catch (Exception) {
            $command->error('WebUser not found');
            return 1;
        }

        if (!$customer->shop->website) {
            $command->error("Shop {$customer->shop->name} do not have website");

            return 1;
        }

        $token = $this->handle($customer, []);
        $command->line("Customer access token: $token");


        return 0;

    }
}
