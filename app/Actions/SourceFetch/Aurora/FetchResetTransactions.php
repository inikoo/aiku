<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 10:29:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

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
                $command->line("🏃 org: $organisation->slug ");
                $this->setAuroraConnection($databaseName, $command->option('db_suffix'));


                $this->timeStart    = microtime(true);
                $this->timeLastStep = microtime(true);

                /*
                DB::connection('aurora')->table('Category Dimension')
                    ->update([
                        'aiku_department_id' => null,
                        'aiku_family_id'     => null
                    ]);
                */
                $command->line('✅ shops');


                DB::connection('aurora')->table('Email Campaign Type Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Email Campaign Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Email Tracking Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Email Tracking Event Dimension')
                    ->update([$aikuIdField => null]);

                $command->line("✅ mailroom \t\t".$this->stepTime());


                DB::connection('aurora')->table('Timesheet Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Timesheet Record Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Clocking Machine Dimension')
                    ->update([$aikuIdField => null]);
                $command->line("✅ HR \t\t\t".$this->stepTime());


                /*
                DB::connection('aurora')->table('Inventory Transaction Fact')
                    ->update([
                        $aikuIdField        => null,
                        'aiku_dn_item_id' => null,
                        'aiku_picking_id' => null,

                    ]);
                */

                $command->line("✅ stock movements \t".$this->stepTime());

                DB::connection('aurora')->table('Product Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Product History Dimension')
                    ->update([$aikuIdField => null]);

                $command->line("✅ products \t\t".$this->stepTime());


                DB::connection('aurora')->table('Customer Favourite Asset Fact')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Back in Stock Reminder Fact')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Customer Portfolio Fact')
                    ->update([$aikuIdField => null]);

                $command->line("✅ customers \t\t".$this->stepTime());


                DB::connection('aurora')->table('Order Dimension')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Order Transaction Fact')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Order No Asset Transaction Fact')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Order Dimension')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Order Transaction Fact')->update(
                    [
                        $aikuIdField => null,
                        // 'aiku_invoice_id' => null,
                    ]
                );
                DB::connection('aurora')->table('Order No Asset Transaction Fact')->update(
                    [
                        $aikuIdField => null,
                        // 'aiku_invoice_id' => null,
                    ]
                );

                DB::connection('aurora')->table('Invoice Dimension')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Invoice Deleted Dimension')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Delivery Note Dimension')->update([$aikuIdField => null]);

                $command->line("✅ orders \t\t".$this->stepTime());
            }
        }

        return $exitCode;
    }

}
