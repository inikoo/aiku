<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 10:30:08 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Search;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithOrganisationsArgument;
use Illuminate\Console\Command;

class UpdateUniversalSearch extends HydrateModel
{
    use WithOrganisationsArgument;

    public string $commandSignature = 'search:update';




    public function asCommand(Command $command): int
    {
        $command->line('Guests');
        $command->call('guests:search');

        $command->line('Workplaces');
        $command->call('workplace:search');
        $command->line('Employees');
        $command->call('employee:search');

        $command->line('Products');
        $command->call('product:search');
        $command->line('Product categories');
        $command->call('product-category:search');

        $command->line('Customers');
        $command->call('customer:search');

        $command->line('Orders');
        $command->call('order:search');

        $command->line('Invoices');
        $command->call('invoice:search');

        $command->line('Employees');
        $command->call('fulfilment-customer:search');


        return 0;

    }

}
