<?php

namespace App\Actions\CRM\Appointment\GoogleCalendar;

use App\Models\Catalogue\Shop;
use App\Services\GoogleCalendar\GoogleCalendarClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsObject;
use Throwable;

/**
 * Read for a few minutes at a time, since every visitor on the booking page asks for the same days.
 * When Google cannot be reached nothing is blocked, so the booking page keeps working.
 */
class GetGoogleCalendarBusyPeriods
{
    use AsObject;

    public const int CACHE_MINUTES = 5;

    /**
     * @return Collection<int, array{0: Carbon, 1: Carbon}>
     */
    public function handle(Shop $shop, Carbon $from, Carbon $to): Collection
    {
        $client = GoogleCalendarClient::forShop($shop);
        if (!$client) {
            return collect();
        }

        $timezone = $shop->timezone?->name ?? 'UTC';
        $cacheKey = 'google-calendar-busy:'.$shop->id.':'.$from->copy()->utc()->format('YmdH').':'.$to->copy()->utc()->format('YmdH');

        try {
            $periods = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_MINUTES), fn () => $client->busyPeriods($from, $to, $timezone)
                ->map(fn (array $period) => [$period[0]->toIso8601String(), $period[1]->toIso8601String()])
                ->all());
        } catch (Throwable $exception) {
            Log::warning('Google Calendar busy times not read', ['shop_id' => $shop->id, 'error' => $exception->getMessage()]);

            return collect();
        }

        return collect($periods)->map(fn (array $period) => [Carbon::parse($period[0]), Carbon::parse($period[1])]);
    }
}
