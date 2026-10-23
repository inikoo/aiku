<?php

/*
 * Author Louis Perez
 * Created on 21-10-2026-11h-13m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Comms\Email;

use App\Actions\Comms\DispatchedEmail\StoreDispatchedEmail;
use App\Actions\Comms\ExternalSubscriberEmailRecipient\StoreExternalSubscriberEmailRecipient;
use App\Actions\Comms\Traits\WithSendBulkEmails;
use App\Actions\OrgAction;
use App\Enums\Comms\Outbox\OutboxBuilderEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\CRM\Appointment\AppointmentStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\DispatchedEmail;
use App\Models\Comms\ExternalSubscriberEmailRecipient;
use App\Models\Comms\Outbox;
use App\Models\CRM\Appointment;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;

class SendNewAppointmentEmailToShop extends OrgAction
{
    use WithSendBulkEmails;

    public string $jobQueue = 'low-priority';

    public static function recipientEmail(Shop $shop): ?string
    {
        if (!Arr::get($shop->settings, 'appointments.notify_shop', false)) {
            return null;
        }

        return Arr::get($shop->settings, 'appointments.notify_email') ?: $shop->email;
    }

    public function handle(int $appointmentId): ?DispatchedEmail
    {
        $appointment  = Appointment::with(['shop', 'appointmentType', 'user'])->find($appointmentId);
        $emailAddress = $appointment ? self::recipientEmail($appointment->shop) : null;

        if (!$emailAddress) {
            return null;
        }

        return $this->sendTo(
            $appointment,
            $emailAddress,
            fn () => $appointment->group->externalSubscriberEmailRecipients()->where('email', $emailAddress)->first()
                ?? StoreExternalSubscriberEmailRecipient::make()->action($appointment->group, [
                    'name'  => $appointment->shop->name,
                    'email' => $emailAddress,
                ])
        );
    }

    /**
     * @param callable(): (User|ExternalSubscriberEmailRecipient) $resolveRecipient
     * @param (callable(): string)|null $makeSubject
     * @param (callable(): ?string)|null $makeIntroHtml
     */
    public function sendTo(Appointment $appointment, string $emailAddress, callable $resolveRecipient, ?callable $makeSubject = null, ?callable $makeIntroHtml = null): ?DispatchedEmail
    {
        /** @var Outbox|null $outbox */
        $outbox = $appointment->shop->outboxes()->where('code', OutboxCodeEnum::NEW_APPOINTMENT->value)->first();
        if (!$outbox || $outbox->state != OutboxStateEnum::ACTIVE || !$outbox->is_applicable || !$outbox->emailOngoingRun?->email?->liveSnapshot) {
            return null;
        }

        $emailHtmlBody = $outbox->builder == OutboxBuilderEnum::BLADE
            ? Arr::get($outbox->emailOngoingRun->email->liveSnapshot->layout, 'blade_template')
            : $outbox->emailOngoingRun->email->liveSnapshot->compiled_layout;
        if (!$emailHtmlBody) {
            return null;
        }

        $recipient = $resolveRecipient();

        $previousLocale = app()->getLocale();
        app()->setLocale($appointment->shop->language->code);

        try {
            $dispatchedEmail = StoreDispatchedEmail::run($outbox->emailOngoingRun, $recipient, [
                'outbox_id'     => $outbox->id,
                'email_address' => $emailAddress,
            ]);
            $dispatchedEmail->refresh();

            return $this->sendEmailWithMergeTags(
                $dispatchedEmail,
                $outbox->emailOngoingRun->sender(),
                $makeSubject ? $makeSubject() : $this->subject($appointment, $outbox),
                $emailHtmlBody,
                '',
                additionalData: [
                    'shop_name'  => $appointment->shop->name,
                    'email_body' => $this->generateBodyHtml($appointment, $makeIntroHtml ? $makeIntroHtml() : null),
                ],
                senderName: $outbox->emailOngoingRun->senderName(),
            );
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    public function subject(Appointment $appointment, Outbox $outbox): string
    {
        $date    = SendAppointmentEmail::make()->localStart($appointment)->translatedFormat('j F Y, H:i');
        $subject = $outbox->emailOngoingRun?->email?->subject;

        if ($subject && $subject !== OutboxCodeEnum::NEW_APPOINTMENT->label()) {
            return str_replace(['[Shop Name]', '[Appointment Date]'], [$appointment->shop->name, $date], $subject);
        }

        return __('New appointment: :name on :date', ['name' => $appointment->contact_name, 'date' => $date]);
    }

    public function generateBodyHtml(Appointment $appointment, ?string $introHtml = null): string
    {
        $appointmentEmail = SendAppointmentEmail::make();
        $start            = $appointmentEmail->localStart($appointment);

        $html = $introHtml ?? $appointmentEmail->paragraph(__(':name booked :type on :date at :time.', [
            'name' => e($appointment->contact_name),
            'type' => e($appointment->appointmentType?->name ?? __('an appointment')),
            'date' => $start->translatedFormat('l j F Y'),
            'time' => $start->format('H:i'),
        ]));

        if ($appointment->state === AppointmentStateEnum::REQUESTED) {
            $html .= $appointmentEmail->paragraph(__('It is waiting for you to accept it.'));
        }

        $html .= $appointmentEmail->detailsTable($appointment, [
            __('Customer')      => $appointment->contact_name,
            __('Email')         => $appointment->email,
            __('Phone')         => $appointment->phone,
            __('People coming') => $appointment->number_visitors > 1 ? (string) $appointment->number_visitors : null,
            __('Notes')         => $appointment->notes,
        ]);

        $url = route('grp.org.shops.show.crm.appointments.show', [
            $appointment->organisation->slug,
            $appointment->shop->slug,
            $appointment->id,
        ]);

        return $html.$appointmentEmail->paragraph('<a href="'.e($url).'">'.__('Open the appointment in Aiku').'</a>');
    }
}
