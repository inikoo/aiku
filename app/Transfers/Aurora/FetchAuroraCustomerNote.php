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
        //   print_r($this->auroraModelData);

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
            $event   = 'updated';
            $tags    = ['crm'];
            $details = null;

            $note          = '';


            $historyDetails = $this->auroraModelData->{'History Details'};
            $historyDetails = preg_replace('/-------------------------------------------/', ';', $historyDetails);

            $changedFields = preg_split('/;\n/', $historyDetails);
            foreach ($changedFields as $changedField) {



                //  $changedField = preg_replace('/ID\/Status - Customer - /', 'ID/Status - ', $changedField);

                $changedField = trim($changedField);

                $changedField = preg_replace('/ -$/', ' - ', $changedField);

                /*
                                if($changedField=='ID/Status -'){
                                    $changedField='ID/Status - ';
                                }elseif($changedField=='Gold Reward Member -'){
                                    $changedField='Gold Reward Member - ';
                                }elseif($changedField=='Trade Name -'){
                                    $changedField='Trade Name - ';
                                }
                */


                // print ">>>$changedField<<<\n";



                list($fieldName, $fieldValue) = explode(' - ', $changedField, 2);

                //  $fieldData = explode(' - ', $changedField);

                //                if (count($fieldData) != 2) {
                //                    dd($this->auroraModelData);
                //                }
                $fieldName  = trim($fieldName);
                $fieldValue = trim(preg_replace('/;$/', '', $fieldValue));


                //print_r($fieldData);

                //  $fieldName  = trim($fieldData[0]);
                //  $fieldValue = trim(preg_replace('/;$/', '', $fieldData[1]));

                if ($fieldName == 'Gold Reward Member') {
                    continue;
                }
                if ($fieldName == 'Email Check' and $fieldValue == '') {
                    continue;
                }

                $newValues[$fieldName] = $fieldValue;
            }

            if (count($newValues) == 0) {
                return;
            }
        }

        // dd($newValues);

        $customer = $this->parseCustomer(
            $this->organisation->id.':'.$this->auroraModelData->{'Indirect Object Key'}
        );

        $this->parsedData['customer'] = $customer;


        $user = null;

        if ($this->auroraModelData->{'Subject'} == 'Staff' and $this->auroraModelData->{'Subject Key'} > 0) {
            $employee = $this->parseEmployee(
                $this->organisation->id.':'.$this->auroraModelData->{'Subject Key'}
            );

            if (!$employee) {
                dd($this->auroraModelData);
            }

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

            if (!$user) {
                DB::connection('aurora')
                    ->table('User Deleted Dimension')
                    ->select('aiku_related_id')
                     ->where('User Parent Key', $this->auroraModelData->{'Subject Key'})
                     ->update(['aiku_related_id' => $employee->id]);

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
