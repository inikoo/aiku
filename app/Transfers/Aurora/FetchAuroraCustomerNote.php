<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Oct 2024 10:17:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\DB;

class FetchAuroraCustomerNote extends FetchAurora
{
    protected function parseModel(): void
    {
        if ($this->auroraModelData->{'Direct Object'} != 'Note') {
            return;
        }

        if ($this->auroraModelData->{'Indirect Object'} != 'Customer') {
            return;
        }

        $details = '';
        if ($this->auroraModelData->{'History Details'}) {
            $details = $this->auroraModelData->{'History Details'};
        }

        $newValues = [];
        $event     = 'customer_note';
        $tags      = ['customer_notes'];
        $note      = $this->auroraModelData->{'History Abstract'};
        if ($note == 'Old Database Note (Attachment)') {
            return;
        } elseif ($note == 'Contact data imported from Act') {
            $event = 'migration';
        } elseif ($note == 'Old Database Note (Field Changed)') {
            $historyDetails = $this->auroraModelData->{'History Details'};
            $historyDetails = preg_replace('/-------------------------------------------/', ';', $historyDetails);

            if ($historyDetails == '') {
                return;
            }

            $event   = 'updated';
            $tags    = ['crm'];
            $details = null;

            $note = '';


            $changedFields = preg_split('/;\n/', $historyDetails);
            foreach ($changedFields as $changedField) {
                $changedField = trim($changedField);
                $changedField = preg_replace('/ -$/', ' - ', $changedField);

                if ($changedField == 'ID/Status - Customer') {
                    continue;
                }
                if ($changedField == 'Not interested') {
                    $changedField = 'ID/Status - Not interested';
                }

                if ($changedField == '') {
                    continue;
                }

                if (!str_contains($changedField, ' - ')) {
                    $changedField = 'Legacy Note - '.$changedField;
                }

                list($fieldName, $fieldValue) = explode(' - ', $changedField, 2);

                $fieldName  = trim($fieldName);
                $fieldValue = trim(preg_replace('/;$/', '', $fieldValue));

                if (in_array($fieldValue, ['.', ',', ':', ';'])) {
                    continue;
                }

                if ($fieldName == 'Gold Reward Member') {
                    continue;
                }
                if ($fieldName == 'Email Check' and in_array($fieldValue, ['', 'Good'])) {
                    continue;
                }
                if ($fieldName == 'ID/Status' and in_array($fieldValue, ['', 'O', '0'])) {
                    continue;
                }

                if ($fieldName == 'Trade Name' and in_array($fieldValue, ['', '0', '.'])) {
                    continue;
                }

                $newValues[$fieldName] = $fieldValue;
            }

            if (count($newValues) == 0) {
                return;
            }
        }


        $customer = $this->parseCustomer(
            $this->organisation->id.':'.$this->auroraModelData->{'Indirect Object Key'}
        );

        if (!$customer) {
            return;
        }

        $this->parsedData['customer'] = $customer;


        $user = null;

        if ($this->auroraModelData->{'Subject'} == 'Staff' and $this->auroraModelData->{'Subject Key'} > 0) {
            $employee = $this->parseEmployee(
                $this->organisation->id.':'.$this->auroraModelData->{'Subject Key'}
            );


            if ($employee) {
                $user = $employee->getUser();
                if (!$user) {
                    $userHasModel = DB::table('user_has_models')
                        ->where('model_id', $employee->id)
                        ->where('model_type', 'Employee')
                        ->first();


                    if ($userHasModel) {
                        $user = User::withTrashed()->find($userHasModel->user_id);
                    }
                }
            }


            if (!$user) {
                $user = User::withTrashed()
                    ->where('group_id', $this->organisation->group_id)
                    ->whereJsonContains('sources->parents', $this->organisation->id.':'.$this->auroraModelData->{'Subject Key'})
                    ->first();
            }

            if (!$user) {
                dd($this->auroraModelData);
            }
        }


        $this->parsedData['customer_note'] =
            [
                'note'            => $note,
                'created_at'      => $this->auroraModelData->{'History Date'},
                'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'History Key'},
                'fetched_at'      => now(),
                'last_fetched_at' => now(),
                'event'           => $event,
                'tags'            => $tags
            ];

        if ($details) {
            if ($this->isHTML($details)) {
                $this->parsedData['customer_note']['note_details_html'] = $details;
            } else {
                $this->parsedData['customer_note']['note_details'] = $details;
            }
        }


        if (count($newValues) > 0) {
            $this->parsedData['customer_note']['new_values'] = $newValues;
        }


        if ($user) {
            $this->parsedData['customer_note']['user_type'] = 'User';
            $this->parsedData['customer_note']['user_id']   = $user->id;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('History Dimension')
            ->where('History Key', $id)->first();
    }

    protected function isHTML($string): bool
    {
        return $string != strip_tags($string);
    }

}
