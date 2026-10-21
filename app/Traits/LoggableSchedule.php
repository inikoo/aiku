<?php

/*
 * Author: Steven Wicca stewicalf@gmail.com
 * Created: Thu, 20 Nov 2025 13:16:33 Central Indonesia Time, Lembeng Beach, Bali, Indonesia
 * Copyright (c) 2025, Steven Wicca Alfredo
 */

namespace App\Traits;

use App\Helpers\ScheduleLogger;
use Exception;
use App\Models\Helpers\ScheduledTaskLog;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Cache;

trait LoggableSchedule
{
    /**
     * Commands run in the background so a long one never holds back the tasks after it in the same
     * schedule:run, which made them start late and miss their Sentry check-in. The log id travels
     * through the cache under the event's mutex name because the after hook of a background command
     * runs in a separate schedule:finish process.
     */
    protected function logSchedule(Event $event, string $name, string $type, string $scheduledAt): Event
    {
        if (!$event instanceof CallbackEvent) {
            $event->runInBackground();
        }

        $key = 'schedule_log:'.$event->mutexName();

        $event->before(function () use ($key, $name, $type, $scheduledAt) {
            Cache::put($key, ScheduleLogger::start($name, $type, $scheduledAt)->id, now()->addDay());
        });

        $event->after(function () use ($key) {
            try {
                $log = ScheduledTaskLog::find(Cache::pull($key));
                if ($log) {
                    ScheduleLogger::finish($log);
                }
            } catch (Exception $e) {
                logger()->error('Failed to finish schedule log: '.$e->getMessage());
            }
        });

        return $event;
    }
}
