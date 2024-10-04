<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 04 Oct 2024 11:58:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraHistory extends FetchAurora
{
    protected function parseModel(): void
    {
        // print_r($this->auroraModelData);
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

        if ($event == 'updated' and count($oldValues) == 0) {
            dd($this->auroraModelData);
        }

        if (count($newValues) == 0) {
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
            ];


        if ($user) {
            $this->parsedData['history']['user_type'] = class_basename($user);
            $this->parsedData['history']['user_id']   = $user->id;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('History Dimension')
            ->where('History Key', $id)->first();
    }


    protected function parseAuditableFromHistory()
    {
        $auditable = null;

        switch ($this->auroraModelData->{'Direct Object'}) {
            case 'Customer':
                $auditable = $this->parseCustomer(
                    $this->organisation->id.':'.$this->auroraModelData->{'Direct Object Key'}
                );
        }


        return $auditable;
    }


    protected function checkIfSkip($auditable, $event): bool
    {
        $skip = false;



        if ($event == 'updated') {

            switch ($auditable) {
                case $auditable instanceof Customer:

                    break;
                case $auditable instanceof Location:
                    $skip = !in_array($this->auroraModelData->{'Indirect Object'}, ['Location Code','Location Max Weight','Location Max Volume']);
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

        return [];
    }

    protected function parseHistoryNewValues($auditable, string $event)
    {
        if ($event == 'created') {
            return $this->parseHistoryCreatedNewValues($auditable);
        } elseif ($event == 'updated') {
            return $this->parseHistoryUpdatedNewValues($auditable);
        }

        return [];
    }


    private function getField()
    {
        return  match ($this->auroraModelData->{'Indirect Object'}) {
            'Location Code' => 'code',
            'Location Max Weight' => 'max_weight',
            'Location Max Volume' => 'max_volume',
            default => $this->auroraModelData->{'Indirect Object'}

        };
    }

    protected function parseHistoryUpdatedOldValues(): array
    {
        $newValues = [];

        $field = $this->getField();

        $haystack  = $this->auroraModelData->{'History Details'};
        if (preg_match('/<div class="field tr"><div>Old value:<\/div><div>(.*)<\/div><\/div>/', $haystack, $matches)) {
            $value = trim($matches[1]);
            $newValues[$field] = $value;

        }

        return $newValues;


    }

    protected function parseHistoryUpdatedNewValues(): array
    {
        $newValues = [];

        $field = $this->getField();

        $haystack  = $this->auroraModelData->{'History Details'};
        if (preg_match('/<div class="field tr"><div>New value:<\/div><div>(.*)<\/div><\/div>/', $haystack, $matches)) {
            $value = trim($matches[1]);
            $newValues[$field] = $value;

        }


        return $newValues;


    }

    protected function parseHistoryCreatedNewValues($auditable): array
    {
        return match (class_basename($auditable)) {
            'Customer' => $this->parseCustomerHistoryCreatedNewValues($auditable),
            'Location' => $this->parseLocationHistoryCreatedNewValues($auditable),
            default => []
        };
    }

    protected function parseCustomerHistoryCreatedNewValues($auditable): array
    {
        $newValues = [];
        $abstract  = $this->auroraModelData->{'History Abstract'};

        if ($abstract == 'Customer Created') {
            $abstract = trim($this->auroraModelData->{'History Details'});
        }

        if ($abstract == 'Utworzono klienta') {
            $abstract = $this->auroraModelData->{'History Details'};
        }

        if ($abstract == 'Compte Client Créé') {
            $abstract = $this->auroraModelData->{'History Details'};
        }

        if ($abstract == 'Customer  registered' ||
            $abstract == 'New customer  added' ||
            $abstract == 'Nouveau Client  ajoute' ||
            $abstract == 'New customer'
        ) {
            $newValues['name'] = '';
        }


        if (preg_match('/(.+) customer record created$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^Customer (.+) registered$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^Kunde (.+) erstellt$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^New customer (.+) added$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^New customer (.+) dodano$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^Byl vytvořen zákaznický záznam\s?(.+)$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^Bol vytvorený zákaznícky záznam\s?(.+)$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^Nouveau Client (.+) soi ajoute$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^Nouveau Client (.+) ajoute$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        } elseif (preg_match('/^New customer (.+)$/', $abstract, $matches)) {
            $newValues['name'] = trim($matches[1]);
        }


        return $newValues;
    }

    protected function parseLocationHistoryCreatedNewValues($auditable): array
    {
        $newValues = [];
        $abstract  = $this->auroraModelData->{'History Abstract'};

        if ($abstract == 'Location Created') {
            $abstract = trim($this->auroraModelData->{'History Details'});
        }


        switch (class_basename($auditable)) {
            case 'Location':
                if (preg_match('/Location (.+) create/', $abstract, $matches)) {
                    $newValues['code'] = trim($matches[1]);
                } elseif (preg_match('/(.+) location record created$/', $abstract, $matches)) {
                    $newValues['code'] = trim($matches[1]);
                } elseif (preg_match('/^New location (.+) added$/', $abstract, $matches)) {
                    $newValues['code'] = trim($matches[1]);
                } elseif (preg_match('/^New location (.+) dodano$/', $abstract, $matches)) {
                    $newValues['code'] = trim($matches[1]);
                } elseif (preg_match('/^New location (.+)$/', $abstract, $matches)) {
                    $newValues['code'] = trim($matches[1]);
                } elseif (preg_match('/^(.+) location created$/', $abstract, $matches)) {
                    $newValues['code'] = trim($matches[1]);
                } elseif (preg_match('/^Poloha(.+) bola vytvorená$/', $abstract, $matches)) {
                    $newValues['code'] = trim($matches[1]);
                }
        }

        return $newValues;
    }

}
