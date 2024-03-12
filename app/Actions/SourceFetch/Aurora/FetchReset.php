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

class FetchReset
{
    use AsAction;
    use WithOrganisationsArgument;
    use WithAttributes;

    public string $commandSignature = 'fetch:reset {organisations?*} {--a|all} {--b|base} {--c|crm} {--o|other}  ';
    private int $timeStart;
    private int $timeLastStep;


    private function setAuroraConnection($databaseName): void
    {
        $databaseSettings = data_get(config('database.connections'), 'aurora');
        data_set($databaseSettings, 'database', $databaseName);
        config(['database.connections.aurora' => $databaseSettings]);
        DB::connection('aurora');
        DB::purge('aurora');
    }

    public function asCommand(Command $command): int
    {

        $aikuIdField     ='aiku_id';
        $aikuGuestIdField='aiku_guest_id';

        if(app()->environment('staging')) {
            $aikuIdField     ='staging_'.$aikuIdField;
            $aikuGuestIdField='staging_'.$aikuGuestIdField;
        }

        $organisations = $this->getOrganisations($command);
        $exitCode      = 0;

        foreach ($organisations as $organisation) {
            if ($databaseName = Arr::get($organisation->source, 'db_name')) {
                $command->line("🏃 org: $organisation->slug ");
                $this->setAuroraConnection($databaseName);

                DB::connection('aurora')->table('pika_fetch')->truncate();
                DB::connection('aurora')->table('pika_fetch_error')->truncate();


                $this->timeStart    = microtime(true);
                $this->timeLastStep = microtime(true);


                if ($command->option('all') || $command->option('base')) {
                    DB::connection('aurora')->table('Staff Dimension')
                        ->update(
                            [
                                $aikuIdField      => null,
                                $aikuGuestIdField => null
                            ]
                        );
                    DB::connection('aurora')->table('Staff Deleted Dimension')
                        ->update(
                            [
                                $aikuIdField      => null,
                                $aikuGuestIdField => null
                            ]
                        );

                    $command->line('✅ hr');
                    DB::connection('aurora')->table('User Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('User Deleted Dimension')
                        ->update([$aikuIdField=> null]);


                    $command->line('✅ sysadmins');

                    DB::connection('aurora')->table('Store Dimension')
                        ->update([$aikuIdField=> null]);

                    DB::connection('aurora')->table('Shipper Dimension')
                        ->update([$aikuIdField=> null]);



                    $command->line('✅ shops');

                    DB::connection('aurora')->table('Warehouse Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Warehouse Area Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Location Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Location Deleted Dimension')
                        ->update([$aikuIdField=> null]);

                    $command->line('✅ websites');
                    DB::connection('aurora')->table('Website Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Page Store Dimension')
                        ->update([$aikuIdField=> null]);


                    $command->line('✅ warehouses');

                    DB::connection('aurora')->table('Agent Dimension')
                        ->update(
                            [
                                $aikuIdField      => null,
                            ]
                        );

                    DB::connection('aurora')->table('Supplier Dimension')
                        ->update(
                            [
                                $aikuIdField         => null,
                            ]
                        );
                    DB::connection('aurora')->table('Supplier Deleted Dimension')
                        ->update(
                            [
                                $aikuIdField         => null,
                            ]
                        );

                    $command->line('✅ agents/suppliers');


                    DB::connection('aurora')->table('Attachment Bridge')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Image Subject Bridge')
                        ->update([$aikuIdField=> null]);

                    $command->line('🆗 base');
                }

                if ($command->option('all') || $command->option('crm')) {

                    DB::connection('aurora')->table('Customer Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Customer Deleted Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Customer Client Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Website User Dimension')
                        ->update([$aikuIdField=> null]);


                    $command->line('🆗 crm');
                }


                if ($command->option('all') || $command->option('other')) {




                    $command->line('✅ base');


                    DB::connection('aurora')->table('Shipping Zone Schema Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Shipping Zone Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Charge Dimension')
                        ->update([$aikuIdField=> null]);

                    /*
                    DB::connection('aurora')->table('Category Dimension')
                        ->update([
                            'aiku_department_id' => null,
                            'aiku_family_id'     => null
                        ]);
                    */
                    $command->line('✅ shops');


                    DB::connection('aurora')->table('Email Campaign Type Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Email Campaign Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Email Tracking Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Email Tracking Event Dimension')
                        ->update([$aikuIdField=> null]);

                    $command->line("✅ mailroom \t\t".$this->stepTime());


                    DB::connection('aurora')->table('Fulfilment Rent Transaction Fact')
                        ->update([$aikuIdField=> null]);

                    DB::connection('aurora')->table('Fulfilment Asset Dimension')
                        ->update([$aikuIdField=> null]);

                    $command->line("✅ warehouses \t\t".$this->stepTime());


                    $command->line("✅ suppliers \t\t".$this->stepTime());


                    DB::connection('aurora')->table('Timesheet Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Timesheet Record Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Clocking Machine Dimension')
                        ->update([$aikuIdField=> null]);
                    $command->line("✅ HR \t\t\t".$this->stepTime());

                    DB::connection('aurora')->table('Part Dimension')
                        ->update([
                          //  'aiku_unit_id' => null,
                            $aikuIdField     => null
                        ]);

                    DB::connection('aurora')->table('Part Deleted Dimension')
                        ->update([$aikuIdField=> null]);

                    $command->line("✅ inventory \t\t".$this->stepTime());



                    DB::connection('aurora')->table('Purchase Order Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Supplier Delivery Dimension')
                        ->update([$aikuIdField=> null]);

                    DB::connection('aurora')->table('Purchase Order Transaction Fact')
                        ->update([$aikuIdField=> null]);

                    $command->line("✅ supplier products and PO \t".$this->stepTime());
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
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Product History Dimension')
                        ->update([$aikuIdField=> null]);

                    $command->line("✅ products \t\t".$this->stepTime());


                    DB::connection('aurora')->table('Customer Favourite Product Fact')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Back in Stock Reminder Fact')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Customer Portfolio Fact')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Website User Dimension')
                        ->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Prospect Dimension')
                        ->update([$aikuIdField=> null]);
                    $command->line("✅ customers \t\t".$this->stepTime());


                    DB::connection('aurora')->table('Order Dimension')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Order Transaction Fact')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Order No Product Transaction Fact')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Order Dimension')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Order Transaction Fact')->update(
                        [
                            $aikuIdField        => null,
                           // 'aiku_invoice_id' => null,
                        ]
                    );
                    DB::connection('aurora')->table('Order No Product Transaction Fact')->update(
                        [
                            $aikuIdField        => null,
                           // 'aiku_invoice_id' => null,
                        ]
                    );

                    DB::connection('aurora')->table('Invoice Dimension')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Invoice Deleted Dimension')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Delivery Note Dimension')->update([$aikuIdField=> null]);

                    DB::connection('aurora')->table('Payment Dimension')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Payment Account Dimension')->update([$aikuIdField=> null]);
                    DB::connection('aurora')->table('Payment Service Provider Dimension')->update([$aikuIdField=> null]);
                    $command->line("✅ orders \t\t".$this->stepTime());
                }
            }
        }

        return $exitCode;
    }

    public function stepTime(): string
    {
        $rollTime           = microtime(true) - $this->timeStart;
        $diff               = microtime(true) - $this->timeLastStep;
        $this->timeLastStep = microtime(true);

        return "\t".round($rollTime, 2).'s'."\t\t".round($diff, 2).'s';
    }
}
