<?php

namespace App\Notifications;

use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Mail\Outbox;
use App\Models\SysAdmin\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->queue= 'urgent';
    }


    public function via($notifiable): array
    {
        return ['mail'];
    }


    public function toMail(WebUser|User $notifiable): MailMessage
    {
        /** @var Outbox $outbox */
        $outbox = $notifiable->shop->outboxes()->where('type', OutboxTypeEnum::PASSWORD_REMINDER->value)
            ->first();

        $data = $outbox->emailTemplate->published_layout ?? [
                'subject'=> 'Reset Password Notification',
                'header' => 'You are receiving this email because we received a password reset request for your account.',
                'action' => 'Reset Password',
                'footer' => 'This password reset link will expire in :count minutes.',
                'notes'  => 'If you did not request a password reset, no further action is required.',
        ];

        $message= (new MailMessage())
            ->subject(Lang::get(Arr::get($data, 'subject')))
            ->line(Lang::get(Arr::get($data, 'header')))
            ->action(Lang::get(Arr::get($data, 'action')), $this->url)
            ->line(Lang::get(Arr::get($data, 'footer'), ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get(Arr::get($data, 'notes')));

        if(app()->isProduction()) {
            if ($notifiable instanceof WebUser) {
                $message->from($notifiable->shop->email);
            } else {
                $message->from($notifiable->group->email);
            }
        }

        return $message;

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
