<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Oct 2024 10:17:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraCustomerNote extends FetchAurora
{
    protected function parseModel(): void
    {
        $customer = $this->parseCustomer(
            $this->organisation->id.':'.$this->auroraModelData->{'Indirect Object Key'}
        );

        $this->parsedData['customer'] = $customer;

        $user = null;

        if ($this->auroraModelData->{'Subject'} == 'Staff') {
            $employee = $this->parseEmployee(
                $this->organisation->id.':'.$this->auroraModelData->{'Subject Key'}
            );
            $user = $employee->getUser();



        }


        $this->parsedData['customer_note'] =
            [
                'note'            => $this->auroraModelData->{'History Abstract'},
                'created_at'      => $this->auroraModelData->{'History Date'},
                'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'History Key'},
                'fetched_at'      => now(),
                'last_fetched_at' => now(),
            ];


        if ($user) {
            $this->parsedData['customer_note']['user_type'] = 'User';
            $this->parsedData['customer_note']['user_id']   = $user->id;
        } else {
            dd($this->auroraModelData);
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('History Dimension')
            ->where('History Key', $id)->first();
    }
}
