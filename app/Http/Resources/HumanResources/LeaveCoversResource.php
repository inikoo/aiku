<?php

namespace App\Http\Resources\HumanResources;

use App\Enums\HumanResources\Leave\LeaveColorEnum;
use App\Models\HumanResources\Leave;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveCoversResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Leave $leave */
        $leave = $this->resource;

        $isOngoing = $leave->start_date->lte(today()) && $leave->end_date->gte(today());
        $hasEnded  = $leave->end_date->lt(today());

        return [
            'id'                    => $leave->id,
            'employee_id'           => $leave->employee_id,
            'employee_name'         => $leave->employee_name,
            'employee_avatar'       => $leave->employee?->avatarUrl(),
            'type_label'            => $leave->leaveType?->name ?? $leave->type,
            'type_color'            => LeaveColorEnum::hexFor($leave->leaveType?->color),
            'start_date'            => $leave->start_date->format('Y-m-d'),
            'end_date'              => $leave->end_date->format('Y-m-d'),
            'status'                => match (true) {
                $isOngoing => 'ongoing',
                $hasEnded  => 'ended',
                default    => 'upcoming',
            },
            'is_ongoing'            => $isOngoing,
            'has_ended'             => $hasEnded,
            'cover_employee_id'     => $leave->cover_employee_id,
            'covered_by'            => $leave->coverEmployee?->contact_name,
            'covered_by_avatar'     => $leave->coverEmployee?->avatarUrl(),
        ];
    }
}
