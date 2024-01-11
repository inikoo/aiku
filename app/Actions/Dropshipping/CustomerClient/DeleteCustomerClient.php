<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Feb 2023 17:11:34 Malaysia Time, Ubud , Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateClients;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithOrganisationArgument;
use App\Models\Dropshipping\CustomerClient;
use Exception;
use Illuminate\Console\Command;

class DeleteCustomerClient
{
    use WithActionUpdate;
    use WithOrganisationArgument;

    public string $commandSignature = 'delete:customer-client {slug}';

    public function handle(CustomerClient $customerClient, array $deletedData=[], bool $skipHydrate = false): CustomerClient
    {
        $customerClient->delete();
        $customerClient=$this->update($customerClient, $deletedData, ['data']);
        if (!$skipHydrate) {
            CustomerHydrateClients::dispatch($customerClient->customer);
        }
        return $customerClient;
    }


    public function asCommand(Command $command): int
    {
        try {
            $customerClient = CustomerClient::where('slug'.$command->argument('slug'))->firstOrFail();
        } catch (Exception) {
            $command->error('Customer Client not found');
            return 1;
        }

        $this->handle($customerClient);

        return 0;
    }
}
