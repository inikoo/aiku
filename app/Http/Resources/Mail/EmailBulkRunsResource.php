<?php
/*
 * author Arya Permana - Kirin
 * created on 12-12-2024-16h-47m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Http\Resources\Mail;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property integer $number_outboxes
 * @property integer $number_mailshots
 * @property integer $number_dispatched_emails
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 *
 */
class EmailBulkRunsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'subject'   => $this->subject,
            'state'     => $this->state,
        ];
    }
}
