<?php

namespace App\Notifications;

use App\Channel\CustomMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEmailRentalAgreementCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public string|null $password;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($password)
    {
        $this->password = $password;
    }


    public function via($notifiable): array
    {
        return ['mail'];
    }


    public function toMail($notifiable): MailMessage
    {
        return (new CustomMailMessage($notifiable))
                    ->line('Here is your credentials to login to web app.')
                    ->line("Username: $notifiable->username")
                    ->line(!$this->password ? "Password: $this->password" : null)
                    ->action('Login', $notifiable->shop->website->domain.'/app/login')
                    ->line('Thank you for using our web app!');
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
