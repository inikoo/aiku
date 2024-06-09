<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 10:29:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class FetchResetBase
{
    use AsAction;
    use WithAuroraOrganisationsArgument;
    use WithAttributes;
    use HasFetchReset;

    public string $commandSignature = 'fetch:reset_base {organisations?*}  {--d|db_suffix=}';
    private int $timeStart;
    private int $timeLastStep;

    public function asCommand(Command $command): int
    {
        $aikuIdField      = 'aiku_id';
        $aikuGuestIdField = 'aiku_guest_id';

        $organisations = $this->getOrganisations($command);
        $exitCode      = 0;

        foreach ($organisations as $organisation) {
            if ($databaseName = Arr::get($organisation->source, 'db_name')) {
                $command->line("🏃 org: $organisation->slug ");
                $this->setAuroraConnection($databaseName, $command->option('db_suffix'));


                DB::connection('aurora')->table('pika_fetch')->truncate();
                DB::connection('aurora')->table('pika_fetch_error')->truncate();


                $this->timeStart    = microtime(true);
                $this->timeLastStep = microtime(true);


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
                    ->update([$aikuIdField => null]);

                //todo: delete this because i think we will not fetch
                /*
                DB::connection('aurora')->table('User Deleted Dimension')
                    ->update([$aikuIdField => null]);
                */


                $command->line('✅ sysadmins');

                DB::connection('aurora')->table('Store Dimension')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Shipper Dimension')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Product Dimension')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Category Dimension')
                    ->update(
                        [
                            'aiku_family_id'    => null,
                            'aiku_department_id'=> null

                        ]
                    );


                $command->line('✅ shops');

                DB::connection('aurora')->table('Warehouse Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Warehouse Area Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Location Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Location Deleted Dimension')
                    ->update([$aikuIdField => null]);

                $command->line('✅ websites');
                DB::connection('aurora')->table('Website Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Page Store Dimension')
                    ->update([$aikuIdField => null]);


                $command->line('✅ warehouses');

                DB::connection('aurora')->table('Agent Dimension')
                    ->update(
                        [
                            $aikuIdField => null,
                        ]
                    );

                DB::connection('aurora')->table('Supplier Dimension')
                    ->update(
                        [
                            $aikuIdField => null,
                        ]
                    );
                DB::connection('aurora')->table('Supplier Deleted Dimension')
                    ->update(
                        [
                            $aikuIdField => null,
                        ]
                    );

                $command->line('✅ agents/suppliers');


                DB::connection('aurora')->table('Attachment Bridge')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Image Subject Bridge')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Customer Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Customer Deleted Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Customer Client Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Website User Dimension')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Shipping Zone Schema Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Shipping Zone Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Charge Dimension')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Fulfilment Rent Transaction Fact')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Fulfilment Asset Dimension')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Part Dimension')
                    ->update([
                        //  'aiku_unit_id' => null,
                        $aikuIdField => null
                    ]);

                DB::connection('aurora')->table('Part Deleted Dimension')
                    ->update([$aikuIdField => null]);

                $command->line("✅ inventory \t\t".$this->stepTime());


                DB::connection('aurora')->table('Purchase Order Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Supplier Delivery Dimension')
                    ->update([$aikuIdField => null]);

                DB::connection('aurora')->table('Purchase Order Transaction Fact')
                    ->update([$aikuIdField => null]);

                $command->line("✅ supplier products and PO \t".$this->stepTime());

                DB::connection('aurora')->table('Website User Dimension')
                    ->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Prospect Dimension')
                    ->update([$aikuIdField => null]);
                $command->line("✅ customers \t\t".$this->stepTime());

                DB::connection('aurora')->table('Payment Dimension')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Payment Account Dimension')->update([$aikuIdField => null]);
                DB::connection('aurora')->table('Payment Service Provider Dimension')->update([$aikuIdField => null]);
                $command->line("✅ payments \t\t".$this->stepTime());

                $command->line('🆗 base');
            }
        }

        return $exitCode;
    }


}
