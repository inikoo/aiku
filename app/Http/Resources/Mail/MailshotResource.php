<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Mail;

use App\Http\Resources\HasSelfCall;
use App\Models\Mail\Mailshot;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class MailshotResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Mailshot $mailshot */
        $mailshot = $this;

        $timelines    = [];
        $timelineData = ['created_at', 'scheduled_at', 'ready_at', 'start_sending_at', 'sent_at', 'cancelled_at', 'stopped_at'];

        foreach ($timelineData as $timeline) {
            $timelineKey = match ($timeline) {
                'start_sending_at' => Str::replace('_at', '', 'sending_at'),
                default            => Str::replace('_at', '', $timeline),
            };

            $timelines[] = [
                'label'     => 'Mailshot '.$timelineKey,
                'icon'      => $timeline == 'created_at' ? 'fal fa-sparkles' : $mailshot->state->stateIcon()[$timelineKey]['icon'],
                //                'timestamp'  => $mailshot->{$timeline} ? $mailshot->{$timeline}->toISOString() : null
                'timestamp' => null
            ];
        }

        $sortedTimeline = collect($timelines)->sortBy(function ($value, $key) {
            return $key;
        })->toArray();

        $newTimeline = [
            [
                'label'     => __('Mailshot created'),
                // 'icon'       => $mailshot->state->stateIcon()['in-process']['icon'],
                'timestamp' => $mailshot->created_at ? $mailshot->created_at : null
            ],
            [
                'label'     => __('Mailshot composed'),
                // 'icon'       => $mailshot->state->stateIcon()['ready']['icon'],
                'timestamp' => $mailshot->ready_at ? $mailshot->ready_at : ($mailshot->start_sending_at ? $mailshot->start_sending_at : null)
            ],
            [
                'label'     => __('Start send'),
                // 'icon'       => $mailshot->state->stateIcon()['sending']['icon'],
                'timestamp' => $mailshot->start_sending_at ? $mailshot->start_sending_at : null
            ],
            [
                'label'     => __('Sent'),
                // 'icon'       => $mailshot->state->stateIcon()['sent']['icon'],
                'timestamp' => $mailshot->sent_at ? $mailshot->sent_at : null
            ]
        ];


        return [
            'id'                  => $mailshot->id,
            'slug'                => $mailshot->slug,
            'subject'             => $mailshot->subject,
            'state'               => $mailshot->state,
            'state_label'         => $mailshot->state->labels()[$mailshot->state->value],
            'state_icon'          => $mailshot->state->stateIcon()[$mailshot->state->value],
            'stats'               => MailshotStatResource::make($mailshot->stats)->getArray(),
            'recipient_stored_at' => $mailshot->recipients_stored_at,
            'schedule_at'         => $mailshot->schedule_at,
            'ready_at'            => $mailshot->ready_at,
            'sent_at'             => $mailshot->sent_at,
            'cancelled_at'        => $mailshot->cancelled_at,
            'stopped_at'          => $mailshot->stopped_at,
            'date'                => $mailshot->date,
            'created_at'          => $mailshot->created_at,
            'updated_at'          => $mailshot->updated_at,
            'timeline'            => $newTimeline,
            'is_layout_blank'     => blank($mailshot->layout),
            'outbox_id'           => $mailshot->outbox_id
        ];
    }
}
