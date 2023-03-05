<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Feb 2023 17:11:34 Malaysia Time, Ubud , Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\Sales\Customer\HydrateCustomer;
use App\Actions\WithActionUpdate;
use App\Models\Dropshipping\CustomerClient;
use Illuminate\Console\Command;

class DeleteCustomerClient
{
    use WithActionUpdate;

    public string $commandSignature = 'delete:customer-client {tenant} {id}';

    public function handle(CustomerClient $customerClient, array $deletedData=[], bool $skipHydrate = false): CustomerClient
    {
        $customerClient->delete();
        $customerClient=$this->update($customerClient, $deletedData, ['data']);
        if (!$skipHydrate) {
            HydrateCustomer::make()->clients($customerClient->customer);
        }
        return $customerClient;
    }


    public function asCommand(Command $command): int
    {
        $tenant = tenancy()->query()->where('code', $command->argument('tenant'))->first();
        tenancy()->initialize($tenant);

        $this->handle(CustomerClient::findOrFail($command->argument('id')));

        return 0;
    }
}
