<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 00:01:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithOrganisationsArgument;
use Illuminate\Console\Command;

class ReindexSearch extends HydrateModel
{
    use WithOrganisationsArgument;

    public string $commandSignature = 'search:reindex {--s|sections=*}';

    public function asCommand(Command $command): int
    {
        if ($this->checkIfCanReindex(['crm'], $command)) {
            $this->reindexCrm($command);
        }

        if ($this->checkIfCanReindex(['fulfilment', 'ful'], $command)) {
            $this->reindexFulfilment($command);
        }

        if ($this->checkIfCanReindex(['inventory', 'inv'], $command)) {
            $this->reindexInventory($command);
        }


        return 0;
    }



    protected function reindexInventory(Command $command): void
    {
        $command->info('Inventory section 📦');
        $command->call('search:warehouses');
        $command->call('search:warehouse_areas');
        $command->call('search:locations');
    }

    protected function reindexCrm(Command $command): void
    {
        $command->info('CRM section 👸🏻');
        $command->call('search:customers');
        $command->call('search:prospects');
    }

    protected function reindexFulfilment(Command $command): void
    {
        $command->info('Fulfillment section 🚛');
        $command->call('search:recurring_bills');
        $command->call('search:fulfilment_customers');
    }

    private function checkIfCanReindex(array $keys, $command): bool
    {
        $result = array_intersect($keys, $command->option('sections'));
        if (count($command->option('sections')) == 0 || count($result)) {
            return true;
        }

        return false;
    }

}
