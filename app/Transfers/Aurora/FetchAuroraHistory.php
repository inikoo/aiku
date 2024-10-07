<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 04 Oct 2024 11:58:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Models\Catalogue\Product;
use App\Models\CRM\Customer;
use App\Models\Inventory\Location;
use Illuminate\Support\Facades\DB;

class FetchAuroraHistory extends FetchAurora
{
    use WithParseUpdateHistory;
    use WithParseCreatedHistory;

    protected function parseModel(): void
    {
        //print_r($this->auroraModelData);
        //enum('sold_since','last_sold','first_sold','placed','wrote','deleted','edited','cancelled','charged','merged','created','associated','disassociate','register','login','logout','fail_login','password_request','password_reset','search')
        $event = match ($this->auroraModelData->{'Action'}) {
            'edited' => 'updated',
            default => $this->auroraModelData->{'Action'}
        };


        if ($event == 'created' and $this->auroraModelData->{'Indirect Object'} != '') {
            dd('xxx');

            return;
        }


        $auditable = $this->parseAuditableFromHistory();
        if (!$auditable) {
            return;
        }

        $skip = $this->checkIfSkip($auditable, $event);
        if ($skip) {
            return;
        }


        $tags = $auditable->generateTags();

        $user = $this->parseUserFromHistory();

        $newValues = $this->parseHistoryNewValues($auditable, $event);
        $oldValues = $this->parseHistoryOldValues($auditable, $event);
        $data = $this->parseHistoryData($auditable, $event);

        if ($event == 'updated' and
            (
                count($oldValues) == 0 or
                count($newValues) == 0
            )

        ) {
            print_r($oldValues);
            print_r($newValues);
            dd($this->auroraModelData);
        }


        $this->parsedData['auditable'] = $auditable;
        $this->parsedData['history']   =
            [

                'created_at'      => $this->auroraModelData->{'History Date'},
                'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'History Key'},
                'fetched_at'      => now(),
                'last_fetched_at' => now(),
                'event'           => $event,
                'tags'            => $tags,
                'new_values'      => $newValues,
                'old_values'      => $oldValues,
                'data'      => $data,
            ];


        if ($user) {
            $this->parsedData['history']['user_type'] = class_basename($user);
            $this->parsedData['history']['user_id']   = $user->id;
        }
        //dd($this->parsedData['history']);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('History Dimension')
            ->where('History Key', $id)->first();
    }


    protected function parseAuditableFromHistory(): Customer|Location|Product|null
    {
        $auditable = null;

        switch ($this->auroraModelData->{'Direct Object'}) {
            case 'Customer':
                $auditable = $this->parseCustomer(
                    $this->organisation->id.':'.$this->auroraModelData->{'Direct Object Key'}
                );
                break;
            case 'Location':
                $auditable = $this->parseLocation(
                    $this->organisation->id.':'.$this->auroraModelData->{'Direct Object Key'},
                    $this->organisationSource
                );
                break;
            case 'Product':
                $auditable = $this->parseProduct($this->organisation->id.':'.$this->auroraModelData->{'Direct Object Key'});
                break;
        }


        return $auditable;
    }


    protected function checkIfSkip($auditable, $event): bool
    {
        $skip = false;


        if ($event == 'updated') {
            $skip = true;
            switch ($auditable) {
                case $auditable instanceof Customer:

                    break;
                case $auditable instanceof Location:
                    $skip = !in_array($this->auroraModelData->{'Indirect Object'}, ['Location Code', 'Location Max Weight', 'Location Max Volume']);
                    break;
                case $auditable instanceof Product:
                    $skip = !in_array($this->auroraModelData->{'Indirect Object'}, ['Product Code', 'Product Price', 'Product Name', 'Product Status']);
                    break;
            }
        }


        return $skip;
    }

    protected function parseHistoryOldValues($auditable, string $event): array
    {
        if ($event == 'created') {
            return [];
        }

        return $this->parseHistoryUpdatedOldValues($auditable);
    }

    protected function parseHistoryNewValues($auditable, string $event): array
    {
        if ($event == 'created') {
            return $this->parseHistoryCreatedNewValues($auditable);
        } elseif ($event == 'updated') {
            return $this->parseHistoryUpdatedNewValues($auditable);
        }

        return [];
    }

    protected function parseHistoryData($auditable, string $event): array
    {
        if ($event == 'created') {
            return $this->parseHistoryCreatedData($auditable);
        }

        return [];
    }


    private function getField()
    {
        return match ($this->auroraModelData->{'Indirect Object'}) {
            'Location Code', 'Product Code' => 'code',
            'Location Max Weight' => 'max_weight',
            'Location Max Volume' => 'max_volume',
            'Product Price' => 'price',
            'Product Status' => 'state',
            'Product Name' => 'name',
            default => $this->auroraModelData->{'Indirect Object'}
        };
    }


}
