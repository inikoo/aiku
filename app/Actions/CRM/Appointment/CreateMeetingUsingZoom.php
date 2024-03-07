<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Appointment;

use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Appointment;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jubaer\Zoom\Facades\Zoom;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateMeetingUsingZoom
{
    use AsAction;
    use WithAttributes;
    use AsCommand;
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Appointment $appointment)
    {
        $zoom = Zoom::createMeeting([
            "agenda"       => 'appointment',
            "topic"        => 'Appointment for ' . $appointment->name,
            "type"         => 2, // 1 => instant, 2 => scheduled, 3 => recurring with no fixed time, 8 => recurring with fixed time
            "duration"     => 60, // in minutes
            "timezone"     => 'UTC', // set your timezone
            "password"     => Str::random(6),
            "start_time"   => $appointment->schedule_at, // set your start time
            "pre_schedule" => false,  // set true if you want to create a pre-scheduled meeting
            "settings"     => [
                'join_before_host'  => false, // if you want to join before host set true otherwise set false
                'host_video'        => false, // if you want to start video when host join set true otherwise set false
                'participant_video' => false, // if you want to start video when participants join set true otherwise set false
                'mute_upon_entry'   => false, // if you want to mute participants when they join the meeting set true otherwise set false
                'waiting_room'      => false, // if you want to use waiting room for participants set true otherwise set false
                'audio'             => 'both', // values are 'both', 'telephony', 'voip'. default is both.
                'auto_recording'    => 'none', // values are 'none', 'local', 'cloud'. default is none.
                'approval_type'     => 0, // 0 => Automatically Approve, 1 => Manually Approve, 2 => No Registration Required
            ],
        ]);

        $appointment->update(['event_address' => Arr::get($zoom['data'], 'join_url')]);
    }
}
