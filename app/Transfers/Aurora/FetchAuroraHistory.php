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

        $tags = $auditable->generateTags();

        $user = $this->parseUserFromHistory();

        $newValues = $this->parseHistoryNewValues($auditable, $event);
        $oldValues = $this->parseHistoryOldValues($auditable, $event);

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
        }

        return [];
    }

    protected function parseHistoryCreatedNewValues($auditable)
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


        switch (class_basename($auditable)) {
            case 'Customer':
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
        }

        return $newValues;
    }

}
