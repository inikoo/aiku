<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Jun 2023 13:57:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Helpers;

use App\Http\Resources\HasSelfCall;
use App\Models\Helpers\Upload;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;

class UploadProgressResource extends JsonResource
{
    use HasSelfCall;

    public Organisation $organisation;
    public function __construct($resource, Organisation $organisation)
    {
        parent::__construct($resource);
        $this->organisation = $organisation;
    }

    public function toArray($request): array
    {
        /** @var Upload $upload */
        $upload = $this;

        return [
            'action_type'  => 'Upload',
            'action_id'    => $upload->id,
            'start_at'     => $upload->created_at,
            'end_at'       => $upload->uploaded_at,
            'last_updated' => $upload->updated_at,
            'total'        => $upload->number_rows,
            'done'         => $upload->number_success + $upload->number_fails,
            'view_route'   => [
                'name'       => 'org.crm.shop.prospects.uploads.show',
                'parameters' => [
                    'shop'      => $this->organisation->shops()->first()->slug,
                    'upload'    => $upload->id,
                ],
            ],
            'data'         => [
                'type'           => $upload->type,
                'filename'       => $upload->filename,
                'number_success' => $upload->number_success,
                'number_fails'   => $upload->number_fails,

            ],


        ];
    }
}
