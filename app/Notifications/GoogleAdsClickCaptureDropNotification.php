<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Notifications;

use App\Notifications\Concerns\SendsMailThroughSes;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoogleAdsClickCaptureDropNotification extends Notification
{
    use SendsMailThroughSes;

    public bool $byEmail = true;

    /**
     * @param array<int, array{shop: string, google_clicks: int, captured_clicks: int, capture_rate: float, url: string}> $shops
     */
    public function __construct(public array $shops, public string $date)
    {
    }

    public function via($notifiable): array
    {
        return $this->byEmail ? ['database', 'mail'] : ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title(),
            'body'  => $this->summary(),
            'type'  => 'google_ads_click_capture_drop',
            'slug'  => 'google-ads-click-capture-'.$this->date,
            'route' => $this->shops[0]['url'] ?? null,
        ];
    }

    public function title(): string
    {
        return trans_choice(
            'Google Ads clicks of :count shop were not fully recorded on :date|Google Ads clicks of :count shops were not fully recorded on :date',
            count($this->shops),
            ['count' => count($this->shops), 'date' => $this->date]
        );
    }

    public function summary(): string
    {
        return collect($this->shops)
            ->map(fn (array $shop) => $shop['shop'].': '.$shop['captured_clicks'].'/'.$shop['google_clicks'].' ('.round($shop['capture_rate'] * 100).'%)')
            ->implode("\n");
    }

    public function toMail($notifiable): MailMessage
    {
        $message = $this->newMailMessage()
            ->subject($this->title())
            ->line(__('Clicks Google Ads reported, against the clicks that reached the shop with their click id, which are the only ones an order can be traced back to:'));

        foreach ($this->shops as $shop) {
            $message->line(__(':shop: :captured of :google clicks recorded (:rate%)', [
                'shop'     => "[{$shop['shop']}]({$shop['url']})",
                'captured' => $shop['captured_clicks'],
                'google'   => $shop['google_clicks'],
                'rate'     => round($shop['capture_rate'] * 100),
            ]));
        }

        return $message->line(__('Below 85% usually means a redirect or a landing page is dropping the gclid.'));
    }
}
