<?php

namespace App\Services\GoogleCalendar;

use App\Models\Catalogue\Shop;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * The shop's appointments calendar. Aiku writes each appointment into it as an event, and reads the
 * time already taken there by anything else so the website does not offer it to visitors.
 */
final class GoogleCalendarClient
{
    public const string SETTINGS_KEY = 'google_calendar';

    public const string SCOPES = 'https://www.googleapis.com/auth/calendar.events';

    public const string EVENT_PROPERTY = 'aiku_appointment_id';

    private const string OAUTH_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const string OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const string API_BASE_URL = 'https://www.googleapis.com/calendar/v3/calendars/primary/';

    public function __construct(private readonly Shop $shop)
    {
    }

    public static function isConnected(Shop $shop): bool
    {
        return filled(Arr::get($shop->settings, self::SETTINGS_KEY.'.refresh_token'))
            && blank(Arr::get($shop->settings, self::SETTINGS_KEY.'.revoked_at'));
    }

    public static function forShop(Shop $shop): ?self
    {
        return self::isConnected($shop) ? new self($shop) : null;
    }

    public static function redirectUri(): string
    {
        return route('grp.google_calendar.callback');
    }

    public static function authorizationUrl(string $state): string
    {
        return self::OAUTH_AUTH_URL.'?'.http_build_query([
            'client_id'              => config('services.google_calendar.client_id'),
            'redirect_uri'           => self::redirectUri(),
            'response_type'          => 'code',
            'scope'                  => self::SCOPES,
            'access_type'            => 'offline',
            'prompt'                 => 'consent',
            'state'                  => $state,
            'include_granted_scopes' => 'true',
        ]);
    }

    public static function exchangeCode(string $code): array
    {
        return Http::asForm()->post(self::OAUTH_TOKEN_URL, [
            'client_id'     => config('services.google_calendar.client_id'),
            'client_secret' => config('services.google_calendar.client_secret'),
            'code'          => $code,
            'redirect_uri'  => self::redirectUri(),
            'grant_type'    => 'authorization_code',
        ])->throw()->json();
    }

    public static function accessTokenCacheKey(Shop $shop): string
    {
        return "google-calendar-access-token:{$shop->id}";
    }

    /**
     * Google accepts the id we give an event, so an appointment always lands on the same event and
     * a retried job updates it instead of adding a second one.
     */
    public static function eventId(int $appointmentId): string
    {
        return 'aikuappointment'.$appointmentId;
    }

    public static function calendarName(string $accessToken): ?string
    {
        return Http::withToken($accessToken)
            ->get(self::API_BASE_URL.'events', ['maxResults' => 1])
            ->throw()
            ->json('summary');
    }

    public function accessToken(): string
    {
        return Cache::remember(self::accessTokenCacheKey($this->shop), now()->addMinutes(50), function () {
            $response = Http::asForm()->post(self::OAUTH_TOKEN_URL, [
                'client_id'     => config('services.google_calendar.client_id'),
                'client_secret' => config('services.google_calendar.client_secret'),
                'refresh_token' => Crypt::decryptString((string) Arr::get($this->shop->settings, self::SETTINGS_KEY.'.refresh_token')),
                'grant_type'    => 'refresh_token',
            ]);

            if ($response->json('error') === 'invalid_grant') {
                $this->markRevoked();
            }

            return $response->throw()->json('access_token');
        });
    }

    /**
     * Google refuses a refresh token for good once it is revoked or expired, so the calendar is
     * left alone until someone reconnects it.
     */
    private function markRevoked(): void
    {
        $settings = $this->shop->settings ?? [];
        data_set($settings, self::SETTINGS_KEY.'.revoked_at', now()->toIso8601String());
        $this->shop->updateQuietly(['settings' => $settings]);
    }

    public function saveEvent(int $appointmentId, array $event): void
    {
        $eventId  = self::eventId($appointmentId);
        $response = $this->request()->put('events/'.$eventId, $event);

        if ($response->notFound()) {
            $response = $this->request()->post('events', [...$event, 'id' => $eventId]);
        }

        $response->throw();
    }

    public function deleteEvent(int $appointmentId): void
    {
        $response = $this->request()->delete('events/'.self::eventId($appointmentId));

        if (!$response->notFound() && $response->status() !== 410) {
            $response->throw();
        }
    }

    /**
     * Time taken in the calendar by anything other than our own appointments. An event marked as
     * free in Google does not block, and an all-day event blocks the whole day.
     *
     * @return Collection<int, array{0: Carbon, 1: Carbon}>
     */
    public function busyPeriods(Carbon $from, Carbon $to, string $timezone): Collection
    {
        $events    = collect();
        $pageToken = null;

        do {
            $response = $this->request()->get('events', array_filter([
                'timeMin'      => $from->copy()->utc()->toIso8601String(),
                'timeMax'      => $to->copy()->utc()->toIso8601String(),
                'singleEvents' => 'true',
                'maxResults'   => 2500,
                'timeZone'     => $timezone,
                'pageToken'    => $pageToken,
            ]))->throw();

            $events    = $events->concat($response->json('items') ?? []);
            $pageToken = $response->json('nextPageToken');
        } while ($pageToken);

        return $events
            ->reject(fn (array $event) => Arr::get($event, 'status') === 'cancelled'
                || Arr::get($event, 'transparency') === 'transparent'
                || filled(Arr::get($event, 'extendedProperties.private.'.self::EVENT_PROPERTY)))
            ->map(fn (array $event) => [
                $this->eventTime(Arr::get($event, 'start', []), $timezone),
                $this->eventTime(Arr::get($event, 'end', []), $timezone),
            ])
            ->filter(fn (array $period) => $period[0] && $period[1])
            ->values();
    }

    private function eventTime(array $time, string $timezone): ?Carbon
    {
        if ($dateTime = Arr::get($time, 'dateTime')) {
            return Carbon::parse($dateTime);
        }

        return ($date = Arr::get($time, 'date')) ? Carbon::parse($date, $timezone) : null;
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->accessToken())->baseUrl(self::API_BASE_URL)->acceptJson();
    }
}
