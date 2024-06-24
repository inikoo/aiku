<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 10:29:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Traits\WithOrganisationsArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class FetchResetTransactions
{
    use AsAction;
    use WithOrganisationsArgument;
    use WithAttributes;
    use HasFetchReset;

    public string $commandSignature = 'fetch:reset_transactions {organisations?*}  {--d|db_suffix=}';
    private int $timeStart;
    private int $timeLastStep;


    public function asCommand(Command $command): int
    {
        $aikuIdField      = 'aiku_id';
        $aikuGuestIdField = 'aiku_guest_id';

        if (app()->environment('staging')) {
            $aikuIdField      = 'staging_'.$aikuIdField;
            $aikuGuestIdField = 'staging_'.$aikuGuestIdField;
        }

        $organisations = $this->getOrganisations($command);
        $exitCode      = 0;

        foreach ($organisations as $organisation) {
            if ($databaseName = Arr::get($organisation->source, 'db_name')) {

                if($databaseName=='wowsbar') {
                    continue;
                }

                $command->line("🏃 org: $organisation->slug ");



                $this->setAuroraConnection($databaseName, $command->option('db_suffix'));


                $this->timeStart    = microtime(true);
                $this->timeLastStep = microtime(true);




                DB::connection('aurora')->table('Email Campaign Type Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Email Campaign Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Email Tracking Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Email Tracking Event Dimension')
                    ->update([$aikuIdField => null]);

                $command->line("✅ post rooms \t\t".$this->stepTime());





            }
        }

        return $exitCode;
    }

}
