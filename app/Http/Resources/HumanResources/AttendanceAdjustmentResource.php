<?php

namespace App\Http\Resources\HumanResources;

use App\Enums\HumanResources\Attendance\AttendanceAdjustmentStatusEnum;
use App\Models\HumanResources\Employee;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JsonSerializable;

/**
 * @property int $id
 * @property string $employee_name
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $original_start_at
 * @property \Illuminate\Support\Carbon|null $original_end_at
 * @property \Illuminate\Support\Carbon|null $requested_start_at
 * @property \Illuminate\Support\Carbon|null $requested_end_at
 * @property string $reason
 * @property AttendanceAdjustmentStatusEnum $status
 */
class AttendanceAdjustmentResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        $timezone  = $this->resource->relationLoaded('organisation') ? ($this->organisation?->timezone?->name ?? 'UTC') : null;
        $localTime = fn (?Carbon $moment): ?string => $timezone && $moment ? $moment->copy()->timezone($timezone)->format('H:i') : null;

        return [
            'id'                  => $this->id,
            'employee_id'         => $this->employee_id,
            'employee_name'       => $this->employee_name,
            'employee_avatar'     => $this->resource->relationLoaded('employee') && $this->employee?->relationLoaded('image') ? $this->employee->avatarUrl() : null,
            'timesheet_id'        => $this->timesheet_id,
            'date'                => $this->date?->format('Y-m-d'),
            'original_start_at'   => $this->original_start_at?->toISOString(),
            'original_end_at'     => $this->original_end_at?->toISOString(),
            'requested_start_at'  => $this->requested_start_at?->toISOString(),
            'requested_end_at'    => $this->requested_end_at?->toISOString(),
            'original_start_time_local'  => $localTime($this->original_start_at),
            'original_end_time_local'    => $localTime($this->original_end_at),
            'requested_start_time_local' => $localTime($this->requested_start_at),
            'requested_end_time_local'   => $localTime($this->requested_end_at),
            'timezone'            => $timezone,
            'reason'              => $this->reason,
            'status'              => $this->status?->value,
            'status_label'        => AttendanceAdjustmentStatusEnum::labels()[$this->status?->value] ?? $this->status?->value,
            'status_color'        => AttendanceAdjustmentStatusEnum::colors()[$this->status?->value] ?? 'gray',
            'status_icon'         => AttendanceAdjustmentStatusEnum::stateIcon()[$this->status?->value] ?? null,
            'approved_by'         => $this->approved_by,
            'approver_name'       => $this->resource->relationLoaded('approver') ? ($this->approver?->contact_name ?: $this->approver?->username) : null,
            'approver_avatar'     => $this->resource->relationLoaded('approver') && $this->approver?->relationLoaded('image') && $this->approver->image
                ? ($this->approver->imageSources(Employee::AVATAR_SIZE, Employee::AVATAR_SIZE)['original'] ?? null)
                : null,
            'approved_at'         => $this->approved_at?->toISOString(),
            'approval_comment'    => $this->approval_comment,
            'created_at'          => $this->created_at?->toISOString(),
            'attachments'         => $this->getMedia('attachments')?->map(fn ($media) => [
                'id'   => $media->id,
                'name' => $media->file_name,
                'url'  => route('grp.media.download', ['media' => $media->ulid]),
            ]),
        ];
    }
}
