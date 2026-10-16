<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-16h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Notifications;

use App\Events\BroadcastPersonalNotification;
use App\Models\Goods\ProductDevelopment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductDevelopmentNotification extends Notification
{
    public bool $byEmail = false;

    public function __construct(public ?ProductDevelopment $productDevelopment, public string $title, public string $body, public ?string $tab = null, public ?string $url = null)
    {
    }

    public function link(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if (!$this->productDevelopment) {
            return route('grp.product_developments.index');
        }

        return route('grp.product_developments.show', array_filter([
            'productDevelopment' => $this->productDevelopment->slug,
            'tab'                => $this->tab,
        ]));
    }

    public function via($notifiable): array
    {
        BroadcastPersonalNotification::dispatch($notifiable->id, ['id' => null, 'title' => $this->title, 'body' => $this->body, 'route' => $this->link()]);

        return $this->byEmail ? ['database', 'mail'] : ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->title)
            ->line($this->body)
            ->action(__('Open in aiku'), $this->link());
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'type'  => 'product_development',
            'route' => $this->link(),
        ];
    }
}
