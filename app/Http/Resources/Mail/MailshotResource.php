<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Mail;

use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Http\Resources\HasSelfCall;
use App\Models\Comms\Mailshot;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MailshotResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Mailshot $mailshot */
        $mailshot = $this;

        // $timelines    = [];
        // $timelineData = ['created_at', 'scheduled_at', 'ready_at', 'start_sending_at', 'sent_at', 'cancelled_at', 'stopped_at'];

        // foreach ($timelineData as $timeline) {
        //     $timelineKey = match ($timeline) {
        //         'start_sending_at' => Str::replace('_at', '', 'sending_at'),
        //         default            => Str::replace('_at', '', $timeline),
        //     };

        //     $timelines[] = [
        //         'label'     => 'Mailshot '.$timelineKey,
        //         'icon'      => $timeline == 'created_at' ? 'fal fa-sparkles' : $mailshot->state->stateIcon()[$timelineKey]['icon'],
        //         //                'timestamp'  => $mailshot->{$timeline} ? $mailshot->{$timeline}->toISOString() : null
        //         'timestamp' => null
        //     ];
        // }

        // $sortedTimeline = collect($timelines)->sortBy(function ($value, $key) {
        //     return $key;
        // })->toArray();

        // $newTimeline = [
        //     [
        //         'label'     => __('Mailshot created'),
        //         'icon'      => $mailshot->state->stateIcon()['in-process']['icon'],
        //         'timestamp' => $mailshot->created_at ?? null,
        //         'current'   => isset($mailshot->created_at),
        //     ],
        //     [
        //         'label'     => __('Mailshot composed'),
        //         'icon'      => $mailshot->state->stateIcon()['ready']['icon'],
        //         'timestamp' => $mailshot->ready_at ?? $mailshot->start_sending_at ?? null,
        //         'current'   => isset($mailshot->ready_at),
        //     ],
        //     [
        //         'label'     => __('Start send'),
        //         'icon'      => $mailshot->state->stateIcon()['sending']['icon'],
        //         'timestamp' => $mailshot->start_sending_at ?? null,
        //         'current'   => isset($mailshot->start_sending_at),
        //     ],
        //     [
        //         'label'     => __('Sent'),
        //         'icon'      => $mailshot->state->stateIcon()['sent']['icon'],
        //         'timestamp' => $mailshot->sent_at ?? null,
        //         'current'   => isset($mailshot->sent_at),
        //     ],
        // ];

        $timeline = [];
        foreach (MailshotStateEnum::cases() as $state) {
            if ($state === MailshotStateEnum::IN_PROCESS) {
                $timestamp = $mailshot->created_at;
            } else {
                $timestamp = $mailshot->{$state->snake().'_at'} ? $mailshot->{$state->snake().'_at'} : null;
            }

            // If all possible values are null, set the timestamp to null explicitly
            $timestamp = $timestamp ?: null;

            $timeline[$state->value] = [
                'label'     => $state->labels()[$state->value],
                'tooltip'   => $state->labels()[$state->value],
                'key'       => $state->value,
                'icon'      => $mailshot->state->stateIcon()[$state->value]['icon'],
                'timestamp' => $timestamp
            ];
        }

        $finalTimeline = Arr::except(
            $timeline,
            [
                $mailshot->state->value == MailshotStateEnum::CANCELLED->value
                    ? MailshotStateEnum::STOPPED->value
                    : MailshotStateEnum::CANCELLED->value
            ]
        );


        $newStats = [
            [
                'label' => __('Error Emails'),
                'key'   => 'number_error_emails',
                'icon'  => 'fal fa-user',
                "class" => 'from-red-500  to-red-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_error_emails'],
            ],
            [
                'label' => __('Rejected Emails'),
                'key'   => 'number_rejected_emails',
                'icon'  => 'fal fa-user',
                "class" => 'from-red-500  to-red-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_rejected_emails'],
            ],
            [
                'label' => __('Sent Emails'),
                'key'   => 'number_sent_emails',
                'icon'  => 'fal fa-user',
                "class" => 'from-blue-500  to-sky-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_sent_emails'],
            ],
            [
                'label' => __('Delivered Emails'),
                'key'   => 'number_delivered_emails',
                'icon'  => 'fal fa-paper-plane',
                "class" => 'from-blue-500  to-sky-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_delivered_emails'],
            ],
            [
                'label' => __('Hard Bounced Emails'),
                'key'   => 'number_hard_bounced_emails',
                'icon'  => 'fal fa-skull',
                "class" => 'from-red-500  to-red-300',
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_hard_bounced_emails'],
            ],
            [
                'label' => __('Soft Bounced Emails'),
                'key'   => 'number_soft_bounced_emails',
                'icon'  => 'fal fa-dungeon',
                "class" => 'from-red-500  to-red-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_soft_bounced_emails'],
            ],
            [
                'label' => __('Opened Emails'),
                'key'   => 'number_opened_emails',
                'icon'  => 'fal fa-envelope-open',
                "class" => 'from-green-500  to-green-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_opened_emails'],
            ],
            [
                'label' => __('Clicked Emails'),
                'key'   => 'number_clicked_emails',
                'icon'  => 'fal fa-hand-pointer',
                "class" => 'from-green-500  to-green-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_clicked_emails'],
            ],
            [
                'label' => __('Spam Emails'),
                'key'   => 'number_spam_emails',
                'icon'  => 'fal fa-eye-slash',
                "class" => 'from-orange-500  to-orange-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_spam_emails'],
            ],
            [
                'label' => __('Unsubscribed Emails'),
                'key'   => 'number_unsubscribed_emails',
                'icon'  => 'fal fa-user-slash',
                "class" => 'from-red-500  to-red-300' ,
                'value' => $value = MailshotStatResource::make($mailshot->stats)->toArray(request())['number_unsubscribed_emails'],
            ],
        ];

        return [
            'id'                  => $mailshot->id,
            'slug'                => $mailshot->slug,
            'subject'             => $mailshot->subject,
            'state'               => $mailshot->state,
            'state_label'         => $mailshot->state->labels()[$mailshot->state->value],
            'state_icon'          => $mailshot->state->stateIcon()[$mailshot->state->value],
            'stats'               => $newStats,
            'recipient_stored_at' => $mailshot->recipients_stored_at,
            'schedule_at'         => $mailshot->schedule_at,
            'ready_at'            => $mailshot->ready_at,
            'sent_at'             => $mailshot->sent_at,
            'cancelled_at'        => $mailshot->cancelled_at,
            'stopped_at'          => $mailshot->stopped_at,
            'date'                => $mailshot->date,
            'created_at'          => $mailshot->created_at,
            'updated_at'          => $mailshot->updated_at,
            'timeline'            => $finalTimeline,
            'is_layout_blank'     => blank($mailshot->layout),
            'outbox_id'           => $mailshot->outbox_id,
            'layout'              => $mailshot->email->snapshot->layout ?? null
        ];
    }
}
