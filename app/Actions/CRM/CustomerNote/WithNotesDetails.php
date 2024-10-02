<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Oct 2024 17:25:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\CustomerNote;

use Illuminate\Support\Arr;

trait WithNotesDetails
{
    private function processNotes($modelData)
    {
        if (!Arr::exists($modelData, 'new_values')) {
            $newValues = ['note' => $modelData['note']];
            if (Arr::exists($modelData, 'note_details_html')) {
                $newValues['details']['html'] = $modelData['note_details_html'];
            }
            if (Arr::exists($modelData, 'note_details')) {
                $newValues['details']['text'] = $modelData['note_details'];
            }

            data_set($modelData, 'new_values', $newValues);
        }
        data_forget($modelData, 'note');
        data_forget($modelData, 'note_details_html');
        data_forget($modelData, 'note_details');


        return $modelData;
    }
}
