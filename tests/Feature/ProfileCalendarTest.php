<?php

use App\Actions\CRM\Appointment\StoreAppointment;
use App\Actions\CRM\AppointmentType\StoreAppointmentType;
use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\Tasks\StoreStaffTask;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\SysAdmin\Guest;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    list($this->organisation, $this->user, $this->shop) = createShop();

    $this->staff = StoreGuest::make()->action(
        $this->organisation->group,
        array_merge(Guest::factory()->definition(), ['positions' => [['slug' => 'group-admin', 'scopes' => []]]])
    )->getUser();
});

test('the profile calendar shows the appointments a user arranges and their task deadlines', function () {
    Queue::fake();

    $nextMonth = now($this->staff->timezone_name)->addMonthNoOverflow()->startOfMonth();

    $appointmentType = StoreAppointmentType::make()->action($this->shop, [
        'name'              => 'Profile calendar '.Str::random(6),
        'meeting_mode'      => AppointmentTypeMeetingModeEnum::STORE_VISIT->value,
        'duration_minutes'  => 45,
        'capacity_per_slot' => 5,
    ]);
    $appointmentType->attendees()->syncWithoutDetaching([$this->staff->id]);

    $appointment = StoreAppointment::make()->action($this->shop, [
        'appointment_type_id' => $appointmentType->id,
        'starts_at'           => $nextMonth->copy()->addDays(9)->setTimezone($this->shop->timezone?->name ?? 'UTC')->format('Y-m-d').' 12:00',
        'contact_name'        => 'Calendar visitor '.Str::random(6),
        'user_id'             => $this->staff->id,
    ]);

    $task = StoreStaffTask::run($this->user, [
        'subject'     => 'Count the showroom shelves',
        'assignee_id' => $this->staff->id,
        'due_at'      => $nextMonth->copy()->addDays(11)->toDateString(),
    ]);

    $response = actingAs($this->staff)
        ->getJson(route('grp.profile.calendar.index', ['month' => $nextMonth->format('Y-m')]))
        ->assertOk();

    $entries = collect($response->json('weeks'))->flatten(1)->pluck('entries')->flatten(1);

    expect($response->json('month'))->toBe($nextMonth->format('Y-m'))
        ->and($response->json('timezone'))->toBe($this->staff->timezone_name)
        ->and($response->json('counts'))->toBe(['appointment' => 1, 'task' => 1, 'ticket' => 0])
        ->and($entries->firstWhere('key', 'appointment-'.$appointment->id)['title'])->toBe($appointment->contact_name)
        ->and($entries->firstWhere('key', 'task-'.$task->id)['date'])->toBe($nextMonth->copy()->addDays(11)->toDateString())
        ->and($entries->firstWhere('key', 'task-'.$task->id)['time'])->toBeNull();

    actingAs($this->staff)
        ->getJson(route('grp.profile.calendar.index', ['month' => 'next month']))
        ->assertUnprocessable();
});

test('the calendar is one of the profile tabs', function () {
    actingAs($this->staff)
        ->getJson(route('grp.profile.page-head-tabs.show'))
        ->assertOk()
        ->assertJsonPath('tabs.navigation.calendar.title', 'Calendar');
});
