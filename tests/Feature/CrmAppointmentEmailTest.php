<?php

use App\Actions\Catalogue\Shop\Seeders\SeedShopOutboxes;
use App\Actions\Comms\Email\SendAppointmentEmail;
use App\Actions\Comms\EmailTemplate\StoreEmailTemplate;
use App\Actions\Comms\Outbox\PublishOutbox;
use App\Actions\Comms\Ses\SendSesEmail;
use App\Actions\CRM\Appointment\StoreAppointment;
use App\Actions\CRM\AppointmentType\StoreAppointmentType;
use App\Enums\Comms\Email\EmailBuilderEnum;
use App\Enums\Comms\EmailTemplate\EmailTemplateStateEnum;
use App\Enums\Comms\Outbox\OutboxBuilderEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Enums\CRM\AppointmentType\AppointmentTypeMeetingModeEnum;
use App\Models\Comms\DispatchedEmail;
use App\Models\Comms\Outbox;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    list($this->organisation, $this->user, $this->shop) = createShop();

    Config::set('inertia.testing.page_paths', [resource_path('js/Pages/Grp')]);
    actingAs($this->user);
});

test('appointment emails are edited in the outbox workshop and sent with their merge tags', function () {
    $layout = json_decode(file_get_contents(database_path('seeders/datasets/email-templates/beefree_appointment_accepted/beefree_appointment_accepted.json')), true);

    StoreEmailTemplate::make()->action($this->organisation->group, [
        'name'        => 'Appointment accepted '.Str::random(6),
        'layout'      => $layout,
        'is_seeded'   => true,
        'builder'     => 'beefree',
        'state'       => EmailTemplateStateEnum::ACTIVE,
        'active_at'   => now(),
        'language_id' => $this->shop->language_id,
        'data'        => ['outboxes' => [OutboxCodeEnum::APPOINTMENT_ACCEPTED->value]],
    ], strict: false);

    /** @var Outbox $outbox */
    $outbox = $this->shop->outboxes()->where('code', OutboxCodeEnum::APPOINTMENT_ACCEPTED->value)->first();
    $outbox->update(['builder' => OutboxBuilderEnum::BLADE, 'state' => OutboxStateEnum::ACTIVE, 'is_applicable' => true]);
    $outbox->emailOngoingRun->email->update(['builder' => EmailBuilderEnum::BLADE]);

    SeedShopOutboxes::run($this->shop);

    $outbox->refresh();
    $email = $outbox->emailOngoingRun->email->refresh();

    expect($outbox->builder)->toBe(OutboxBuilderEnum::BEEFREE)
        ->and($email->builder)->toBe(EmailBuilderEnum::BEEFREE)
        ->and($email->unpublishedSnapshot->layout)->toBe($layout)
        ->and($email->liveSnapshot->compiled_layout)->toBeNull();

    $this->get(route('grp.org.shops.show.dashboard.comms.outboxes.workshop', [$this->organisation->slug, $this->shop->slug, $outbox->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Org/Web/Workshop/Outbox/OutboxWorkshop')
            ->where('mergeTags', fn ($tags) => collect($tags)->pluck('value')->contains('[Appointment Date]')));

    Queue::fake();
    $appointmentType = StoreAppointmentType::make()->action($this->shop, [
        'name'             => 'Workshop email '.Str::random(6),
        'meeting_mode'     => AppointmentTypeMeetingModeEnum::STORE_VISIT->value,
        'location'         => 'Showroom, Sheffield',
        'duration_minutes' => 30,
    ]);
    $appointment = StoreAppointment::make()->action($this->shop, [
        'appointment_type_id' => $appointmentType->id,
        'starts_at'           => now()->addDays(3)->format('Y-m-d').' 10:00',
        'contact_name'        => '<b>Workshop visitor</b>',
        'email'               => 'workshop-'.Str::lower(Str::random(8)).'@example.com',
        'state'               => AppointmentStateEnum::ACCEPTED->value,
    ]);

    $sentHtml = [];
    SendSesEmail::shouldRun()->andReturnUsing(function (string $subject, string $emailHtmlBody, DispatchedEmail $dispatchedEmail) use (&$sentHtml) {
        $sentHtml[] = $emailHtmlBody;

        return $dispatchedEmail;
    });

    SendAppointmentEmail::make()->handle($appointment->id, OutboxCodeEnum::APPOINTMENT_ACCEPTED->value);

    PublishOutbox::make()->action($outbox, [
        'layout'          => $layout,
        'compiled_layout' => '<p>Dear [Customer Name], see you on [Appointment Date] at [Appointment Time] in [Shop Name].</p>[Appointment Details]',
    ]);

    SendAppointmentEmail::make()->handle($appointment->id, OutboxCodeEnum::APPOINTMENT_ACCEPTED->value);

    $startsAt = $appointment->starts_at->copy()->setTimezone($this->shop->timezone?->name ?? 'UTC');

    expect($sentHtml)->toHaveCount(2)
        ->and($sentHtml[0])->toContain('is confirmed')
        ->and($sentHtml[1])->toContain('Dear &lt;b&gt;Workshop visitor&lt;/b&gt;')
        ->and($sentHtml[1])->toContain($startsAt->translatedFormat('l j F Y').' at '.$startsAt->format('H:i'))
        ->and($sentHtml[1])->toContain('Showroom, Sheffield')
        ->and($sentHtml[1])->not->toContain('[Appointment Date]')
        ->and($sentHtml[1])->not->toContain('<b>Workshop visitor</b>');
});
