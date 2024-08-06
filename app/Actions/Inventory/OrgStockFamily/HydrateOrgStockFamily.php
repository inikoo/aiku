<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Aug 2024 12:03:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockFamily;

use App\Actions\Inventory\OrgStockFamily\Hydrators\OrgStockFamilyHydrateOrgStocks;
use App\Models\Inventory\OrgStockFamily;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateOrgStockFamily
{
    use AsAction;
    public string $commandSignature = 'org-stock-family:hydrate {--s|slug=}';

    public function handle(OrgStockFamily $orgStockFamily): void
    {
        OrgStockFamilyHydrateOrgStocks::run($orgStockFamily);
    }

    public function asCommand(Command $command): int
    {
        if($command->option('slug')) {
            try {
                $orgStockFamily = OrgStockFamily::where('slug', $command->option('slug'))->firstorFail();
                $this->handle($orgStockFamily);
                return 0;
            } catch (Exception $e) {
                $command->error($e->getMessage());
                return 1;
            }
        } else {
            $command->withProgressBar(OrgStockFamily::withTrashed()->get(), function ($orgStockFamily) {
                if ($orgStockFamily) {
                    $this->handle($orgStockFamily);
                }
            });
            $command->info("");
        }

        return 0;
    }


}
