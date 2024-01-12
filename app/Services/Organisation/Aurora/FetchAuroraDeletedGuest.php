<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 19:02:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedGuest extends FetchAurora
{
    use WithAuroraImages;
    use WithAuroraParsers;



    protected function parseModel(): void
    {
        $auDeletedModel            = json_decode(gzuncompress($this->auroraModelData->{'Staff Deleted Metadata'}));
        $this->parsedData['guest'] =
            [
                'alias'                    => $this->auroraModelData->{'Staff Alias'},
                'contact_name'             => $auDeletedModel->data->{'Staff Name'},
                'email'                    => $auDeletedModel->data->{'Staff Email'},
                'phone'                    => $auDeletedModel->data->{'Staff Telephone'},
                'identity_document_number' => $auDeletedModel->data->{'Staff Official ID'},
                'date_of_birth'            => $this->parseDate($auDeletedModel->data->{'Staff Birthday'}),
                'created_at'               => $this->parseDate($auDeletedModel->data->{'Staff Valid From'}),
                'source_id'                => $this->organisation->id.':'.$auDeletedModel->data->{'Staff Key'},
                'data'                     => [
                    'address' => $auDeletedModel->data->{'Staff Address'},
                ],
                'deleted_at'               => $this->auroraModelData->{'Staff Deleted Date'}
            ];
    }

    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Staff Deleted Dimension')
            ->where('Staff Deleted Key', $id)->first();
    }
}
