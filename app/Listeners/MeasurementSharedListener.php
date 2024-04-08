<?php

namespace App\Listeners;

use App\Events\BroadcastFulfilmentCustomerNotification;
use App\Notifications\MeasurementShareNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class MeasurementSharedListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BroadcastFulfilmentCustomerNotification $event): void
    {
        foreach ($event->group->users as $user) {
            $user->notify(new MeasurementShareNotification($event->data));
        }
    }
}
