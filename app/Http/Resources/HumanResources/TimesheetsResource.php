<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Oct 2021 12:37:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\HumanResources;

use App\Models\HumanResources\Timesheet;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TimesheetsResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var Timesheet $timesheet */
        $timesheet = $this;

        return [
            'id'                                       => $timesheet->id,
            'organisation_id'                          => $timesheet->organisation_id,
            'organisation_slug'                        => $timesheet->organisation->slug,
            'slug'                                     => $timesheet->slug,
            'date'                                     => $timesheet->date,
            'start_at'                                 => $timesheet->start_at,
            'end_at'                                   => $timesheet->end_at,
            'working_duration'                         => $timesheet->working_duration,
            'breaks_duration'                          => $timesheet->breaks_duration,
            'number_time_trackers'                     => $timesheet->number_time_trackers,
            'number_open_time_trackers'                => $timesheet->number_open_time_trackers,

            'created_at' => $timesheet->created_at,
            'updated_at' => $timesheet->updated_at,
        ];
    }
}
