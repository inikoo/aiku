<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 07 Mar 2024 12:06:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Iris\Appointment;

use App\Models\CRM\Appointment;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowPublicAppointment
{
    use AsAction;

    public function handle(): array
    {
        $calendars = [];

        $appointments = Appointment::whereMonth('schedule_at', now()->format('m'))->get();

        foreach ($appointments as $appointment) {
            $calendars[$appointment->schedule_at->format('Y-m-d')][] = $appointment->schedule_at->format('H:i');
        }

        return $calendars;
    }

    public function htmlResponse(array $calendars): Response
    {
        return Inertia::render('Appointment');
    }
}
