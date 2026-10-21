<?php

use App\Actions\CRM\Appointment\GoogleCalendar\CallbackShopGoogleCalendar;
use App\Actions\CRM\Appointment\GoogleCalendar\DisconnectShopGoogleCalendar;
use App\Actions\CRM\Appointment\GoogleCalendar\ReconcileShopGoogleCalendar;
use App\Actions\CRM\Appointment\GoogleCalendar\SyncAppointmentGoogleCalendarEvent;
use App\Actions\CRM\Appointment\StoreAppointment;
use App\Actions\CRM\Appointment\UpdateAppointment;
use App\Actions\CRM\AppointmentType\GetAppointmentTypeAvailableSlots;
use App\Actions\CRM\AppointmentType\StoreAppointmentType;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Appointment;
use App\Models\CRM\AppointmentType;
use App\Services\GoogleCalendar\GoogleCalendarClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Decorators\JobDecorator;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    list($this->organisation, $this->user, $this->shop) = createShop();

    Http::preventStrayRequests();

    $this->connectCalendar = function (): Shop {
        $settings = $this->shop->settings ?? [];
        data_set($settings, GoogleCalendarClient::SETTINGS_KEY, [
            'calendar'      => 'Showroom',
            'refresh_token' => Crypt::encryptString('refresh-token'),
            'connected_at'  => now()->toIso8601String(),
        ]);
        $this->shop->update(['settings' => $settings]);

        return $this->shop->refresh();
    };

    $this->fakeGoogle = function (array $calendarEvents = []): void {
        Http::fake(function (Request $request) use ($calendarEvents) {
            if (str_contains($request->url(), 'oauth2.googleapis.com/token')) {
                return Http::response(['access_token' => 'access-token', 'refresh_token' => 'refresh-token', 'expires_in' => 3600]);
            }

            return match ($request->method()) {
                'PUT'    => Http::response(['error' => ['code' => 404]], 404),
                'POST'   => Http::response(['id' => Arr::get($request->data(), 'id')]),
                'DELETE' => Http::response(null, 204),
                default  => Http::response(['summary' => 'Showroom', 'items' => $calendarEvents]),
            };
        });
    };

    $this->appointmentType = function (array $modelData = []): AppointmentType {
        $appointmentType = StoreAppointmentType::make()->action($this->shop, [
            'name'              => 'Calendar '.Str::random(6),
            'meeting_mode'      => AppointmentTypeMeetingModeEnum::STORE_VISIT->value,
            'location'          => 'Showroom, Sheffield',
            'duration_minutes'  => 60,
            'capacity_per_slot' => 3,
            ...$modelData,
        ]);
        $appointmentType->attendees()->syncWithoutDetaching([$this->user->id]);

        return $appointmentType;
    };
});

afterEach(function () {
    DisconnectShopGoogleCalendar::make()->handle($this->shop->refresh());
});

test('connecting the shop calendar keeps the token encrypted and writes its upcoming appointments', function () {
    Queue::fake();
    ($this->fakeGoogle)();

    $upcoming = StoreAppointment::make()->action($this->shop, [
        'appointment_type_id' => ($this->appointmentType)()->id,
        'starts_at'           => now($this->shop->timezone?->name ?? 'UTC')->addDays(9)->format('Y-m-d').' 12:00',
        'contact_name'        => 'Calendar visitor '.Str::random(6),
    ]);

    $pushedBeforeConnecting = Queue::pushed(JobDecorator::class, fn ($job) => $job->displayName() === SyncAppointmentGoogleCalendarEvent::class)->count();

    $shop = CallbackShopGoogleCalendar::make()->handle($this->shop, 'oauth-code', ['shop_id' => $this->shop->id, 'user_id' => $this->user->id, 'return' => '/']);

    $pushedIds = Queue::pushed(JobDecorator::class, fn ($job) => $job->displayName() === SyncAppointmentGoogleCalendarEvent::class)
        ->map(fn ($job) => $job->getParameters()[0]);

    expect($pushedBeforeConnecting)->toBe(0)
        ->and(GoogleCalendarClient::isConnected($shop))->toBeTrue()
        ->and(Arr::get($shop->settings, 'google_calendar.calendar'))->toBe('Showroom')
        ->and(Arr::get($shop->settings, 'google_calendar.refresh_token'))->not->toBe('refresh-token')
        ->and(Crypt::decryptString(Arr::get($shop->settings, 'google_calendar.refresh_token')))->toBe('refresh-token')
        ->and($pushedIds->contains($upcoming->id))->toBeTrue();
});

test('the connect button sends the shop admin to Google', function () {
    config(['services.google_calendar.client_id' => 'calendar-client-id', 'services.google_calendar.client_secret' => 'calendar-client-secret']);

    actingAs($this->user)
        ->get(route('grp.org.shops.show.settings.google_calendar.connect', [$this->organisation->slug, $this->shop->slug]))
        ->assertRedirectContains('accounts.google.com')
        ->assertRedirectContains(urlencode(GoogleCalendarClient::SCOPES))
        ->assertRedirectContains('client_id=calendar-client-id');
});

test('a configured redirect uri replaces the callback route when sending the shop admin to Google', function () {
    config([
        'services.google_calendar.client_id'     => 'calendar-client-id',
        'services.google_calendar.client_secret' => 'calendar-client-secret',
        'services.google_calendar.redirect_uri'  => 'http://localhost:8765/google-calendar/callback',
    ]);

    actingAs($this->user)
        ->get(route('grp.org.shops.show.settings.google_calendar.connect', [$this->organisation->slug, $this->shop->slug]))
        ->assertRedirectContains('redirect_uri='.urlencode('http://localhost:8765/google-calendar/callback'));
});

test('the connect button explains the missing credentials instead of sending the shop admin to Google', function () {
    config(['services.google_calendar.client_id' => null, 'services.google_calendar.client_secret' => null]);

    actingAs($this->user)
        ->from(route('grp.org.shops.show.settings.edit', [$this->organisation->slug, $this->shop->slug]))
        ->get(route('grp.org.shops.show.settings.google_calendar.connect', [$this->organisation->slug, $this->shop->slug]))
        ->assertRedirect(route('grp.org.shops.show.settings.edit', [$this->organisation->slug, $this->shop->slug]))
        ->assertSessionHas('notification.status', 'error');
});

test('an appointment is written into the shop calendar and removed when it is cancelled', function () {
    ($this->connectCalendar)();
    Queue::fake();

    $appointment = StoreAppointment::make()->action($this->shop, [
        'appointment_type_id' => ($this->appointmentType)()->id,
        'starts_at'           => now($this->shop->timezone?->name ?? 'UTC')->addDays(9)->format('Y-m-d').' 12:00',
        'contact_name'        => 'Calendar visitor '.Str::random(6),
        'email'               => 'calendar-'.Str::lower(Str::random(8)).'@example.com',
        'user_id'             => $this->user->id,
    ]);

    Queue::assertPushed(JobDecorator::class, fn ($job) => $job->displayName() === SyncAppointmentGoogleCalendarEvent::class && $job->getParameters()[0] === $appointment->id);

    ($this->fakeGoogle)();
    SyncAppointmentGoogleCalendarEvent::run($appointment->id);

    $eventId = GoogleCalendarClient::eventId($appointment->id);
    Http::assertSent(fn (Request $request) => $request->method() === 'POST'
        && Arr::get($request->data(), 'id') === $eventId
        && Arr::get($request->data(), 'status') === 'confirmed'
        && Arr::get($request->data(), 'location') === 'Showroom, Sheffield'
        && Arr::get($request->data(), 'extendedProperties.private.'.GoogleCalendarClient::EVENT_PROPERTY) === (string) $appointment->id
        && str_contains(Arr::get($request->data(), 'summary'), $appointment->contact_name));

    UpdateAppointment::make()->action($appointment, ['state' => AppointmentStateEnum::CANCELLED->value]);
    SyncAppointmentGoogleCalendarEvent::run($appointment->id);

    Http::assertSent(fn (Request $request) => $request->method() === 'DELETE' && str_ends_with($request->url(), 'events/'.$eventId));
});

test('time taken in the shop calendar is not offered to website visitors', function () {
    $timezone        = $this->shop->timezone?->name ?? 'UTC';
    $appointmentType = ($this->appointmentType)([
        'min_notice_hours'    => 0,
        'booking_window_days' => 3,
        'availability'        => ['weekly' => array_fill_keys(range(1, 7), [['from' => '09:00', 'to' => '13:00']])],
    ]);
    $tomorrow = now($timezone)->addDay()->format('Y-m-d');
    $at       = fn (string $time) => ['dateTime' => Carbon::parse($tomorrow.' '.$time, $timezone)->toIso8601String()];

    $slotsBeforeConnecting = GetAppointmentTypeAvailableSlots::run($appointmentType)[$tomorrow];

    ($this->connectCalendar)();
    ($this->fakeGoogle)([
        ['status' => 'confirmed', 'start' => $at('10:00'), 'end' => $at('11:00')],
        ['status' => 'confirmed', 'start' => $at('11:00'), 'end' => $at('12:00'), 'extendedProperties' => ['private' => [GoogleCalendarClient::EVENT_PROPERTY => '1']]],
        ['status' => 'confirmed', 'start' => $at('12:00'), 'end' => $at('13:00'), 'transparency' => 'transparent'],
    ]);

    $slots = GetAppointmentTypeAvailableSlots::run($appointmentType->refresh())[$tomorrow];

    expect($slotsBeforeConnecting)->toBe(['09:00', '10:00', '11:00', '12:00'])
        ->and($slots)->toBe(['09:00', '11:00', '12:00']);
});

test('syncing brings the shop calendar back to the appointments in aiku', function () {
    ($this->connectCalendar)();
    Queue::fake();

    $timezone        = $this->shop->timezone?->name ?? 'UTC';
    $appointmentType = ($this->appointmentType)();
    $book            = fn (int $days) => StoreAppointment::make()->action($this->shop, [
        'appointment_type_id' => $appointmentType->id,
        'starts_at'           => now($timezone)->addDays($days)->format('Y-m-d').' 12:00',
        'contact_name'        => 'Calendar visitor '.Str::random(6),
    ]);

    $missingFromGoogle = $book(11);
    $alreadyInGoogle   = $book(12);
    $cancelled         = $book(13);
    UpdateAppointment::make()->action($cancelled, ['state' => AppointmentStateEnum::CANCELLED->value]);

    $sync           = SyncAppointmentGoogleCalendarEvent::make();
    $deletedId      = (int) Appointment::withTrashed()->max('id') + 1000;
    $orphanEvent    = [...$sync->event($cancelled->refresh()), 'extendedProperties' => ['private' => [GoogleCalendarClient::EVENT_PROPERTY => (string) $deletedId]]];
    $unrelatedEvent = ['summary' => 'Team lunch', 'start' => ['dateTime' => now()->addDays(11)->toIso8601String()], 'end' => ['dateTime' => now()->addDays(11)->addHour()->toIso8601String()]];

    ($this->fakeGoogle)([
        ['id' => GoogleCalendarClient::eventId($alreadyInGoogle->id), ...$sync->event($alreadyInGoogle->refresh())],
        ['id' => GoogleCalendarClient::eventId($cancelled->id), ...$sync->event($cancelled)],
        ['id' => GoogleCalendarClient::eventId($deletedId), ...$orphanEvent],
        ['id' => 'someoneelse', ...$unrelatedEvent],
    ]);

    actingAs($this->user)
        ->from(route('grp.org.shops.show.settings.edit', [$this->organisation->slug, $this->shop->slug]))
        ->post(route('grp.org.shops.show.settings.google_calendar.sync', [$this->organisation->slug, $this->shop->slug]))
        ->assertSessionHas('notification.status', 'success');

    Queue::assertPushed(JobDecorator::class, fn ($job) => $job->displayName() === ReconcileShopGoogleCalendar::class);
    Http::assertNothingSent();

    ReconcileShopGoogleCalendar::make()->handle($this->shop->refresh());

    $writtenIds = collect(Http::recorded())
        ->map(fn (array $exchange) => $exchange[0])
        ->filter(fn (Request $request) => in_array($request->method(), ['PUT', 'POST']) && str_contains($request->url(), '/events'))
        ->map(fn (Request $request) => Arr::get($request->data(), 'extendedProperties.private.'.GoogleCalendarClient::EVENT_PROPERTY))
        ->unique()
        ->values();

    expect($writtenIds->all())->toBe([(string) $missingFromGoogle->id]);
    Http::assertSent(fn (Request $request) => $request->method() === 'DELETE' && str_ends_with($request->url(), 'events/'.GoogleCalendarClient::eventId($cancelled->id)));
    Http::assertSent(fn (Request $request) => $request->method() === 'DELETE' && str_ends_with($request->url(), 'events/'.GoogleCalendarClient::eventId($deletedId)));
    Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'events/someoneelse'));
});

test('the calendar sync command runs and an event at the same moment in another timezone counts as unchanged', function () {
    $this->artisan('appointments:reconcile-google-calendar')->assertSuccessful();

    expect(ReconcileShopGoogleCalendar::make()->sameEvent(
        ['summary' => 'Visit', 'status' => 'confirmed', 'start' => ['dateTime' => '2026-11-02T12:00:00+00:00'], 'end' => ['dateTime' => '2026-11-02T13:00:00+00:00']],
        ['summary' => 'Visit', 'status' => 'confirmed', 'start' => ['dateTime' => '2026-11-02T13:00:00+01:00'], 'end' => ['dateTime' => '2026-11-02T14:00:00+01:00']],
    ))->toBeTrue()
        ->and(ReconcileShopGoogleCalendar::make()->sameEvent(
            ['summary' => 'Visit', 'description' => "Accepted\nhttp://app.aiku.test/appointments/1", 'status' => 'confirmed', 'start' => ['dateTime' => '2026-11-02T12:00:00+00:00'], 'end' => ['dateTime' => '2026-11-02T13:00:00+00:00']],
            ['summary' => 'Visit', 'description' => "Accepted\nhttps://app.aiku.test/appointments/1", 'status' => 'confirmed', 'start' => ['dateTime' => '2026-11-02T12:00:00+00:00'], 'end' => ['dateTime' => '2026-11-02T13:00:00+00:00']],
        ))->toBeTrue();
});
