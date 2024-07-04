<?php

namespace App\Notifications;

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


    public function via($notifiable):array
    {
        return ['mail'];
    }


    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())

                    ->line('Here is your credentials to login to retina web app.')
                    ->line("Username: $notifiable->username")
                    ->line("Password: $this->password")
                    ->action('Login', url('/'))
                    ->line('Thank you for using our application!');
    }


    public function toArray($notifiable):array
    {
        return [
            //
        ];
    }
}
