<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 12 Oct 2026 12:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

namespace App\Actions\Comms\Mailshot\UI;

use App\Actions\Comms\Mailshot\PrepareMailshotAbTestRecipients;
use App\Enums\Comms\Mailshot\MailshotAbTestWinningMetricEnum;
use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Enums\Comms\Mailshot\MailshotTypeEnum;
use App\Enums\Web\WebsiteConversionEvent\WebsiteConversionEventTypeEnum;
use App\Enums\Web\WebsiteVisitor\WebsiteVisitorChannelEnum;
use App\Models\Comms\Mailshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetMailshotAbTest
{
    use AsAction;

    public function handle(Mailshot $mailshot, bool $canEdit): ?array
    {
        $original = $mailshot->isAbTestVariant() ? $mailshot->abTestOriginal : $mailshot;

        if (!$original || $original->is_second_wave || !in_array($original->type, [MailshotTypeEnum::MARKETING, MailshotTypeEnum::NEWSLETTER])) {
            return null;
        }

        $variant          = $original->abTestVariant;
        $hasRemainingList = $original->ab_test_size_percentage < 100;
        $bothSent         = $variant && $original->state === MailshotStateEnum::SENT && $variant->state === MailshotStateEnum::SENT;
        $trackingEndsAt   = $original->abTestTrackingEndsAt();
        $isTrackingOver   = $bothSent && !$trackingEndsAt?->isFuture();
        $versions         = $variant ? [$this->version('A', $original, $trackingEndsAt), $this->version('B', $variant, $trackingEndsAt)] : [];
        $canEditSettings  = $canEdit && !$mailshot->isAbTestVariant() && in_array($original->state, [MailshotStateEnum::IN_PROCESS, MailshotStateEnum::READY]);

        return [
            'enabled'                   => (bool)$variant,
            'is_variant'                => $mailshot->isAbTestVariant(),
            'can_edit_settings'         => $canEditSettings,
            'estimated_recipients'      => $canEditSettings && $variant ? (PrepareMailshotAbTestRecipients::make()->audienceQuery($original)?->count('customers.id') ?? 0) : null,
            'winning_metric'            => $original->ab_test_winning_metric->value,
            'winning_metric_options'    => collect(MailshotAbTestWinningMetricEnum::cases())->map(fn (MailshotAbTestWinningMetricEnum $metric) => [
                'value' => $metric->value,
                'label' => $metric->label(),
            ])->all(),
            'is_blocked_by_second_wave' => $original->is_second_wave_enabled,
            'size_percentage'           => $original->ab_test_size_percentage,
            'winner_mailshot_id'        => $original->ab_test_winner_mailshot_id,
            'winner_sent_at'            => $original->ab_test_winner_sent_at,
            'auto_send_winner'          => $original->ab_test_auto_send_winner,
            'can_send_winner'           => $canEdit && $isTrackingOver && $hasRemainingList && !$original->ab_test_winner_sent_at,
            'variant_delay_seconds'     => $original->ab_test_variant_delay_seconds,
            'variant_sends_at'          => $variant && !$variant->start_sending_at ? $original->abTestVariantSendsAt() : null,
            'tracking_hours'            => $original->ab_test_tracking_hours,
            'tracking_ends_at'          => $trackingEndsAt,
            'suggested_winner_id'       => $isTrackingOver ? $this->suggestedWinnerId($versions, $original->ab_test_winning_metric) : null,
            'versions'                  => $versions,
            'update_route'              => [
                'name'       => 'grp.models.shop.mailshot.ab-test',
                'parameters' => [
                    'shop'     => $original->shop_id,
                    'mailshot' => $original->id,
                ],
            ],
            'send_winner_route'         => [
                'name'       => 'grp.models.shop.mailshot.ab-test.winner',
                'parameters' => [
                    'shop'     => $original->shop_id,
                    'mailshot' => $original->id,
                ],
            ],
        ];
    }

    private function version(string $label, Mailshot $mailshot, ?Carbon $trackingEndsAt): array
    {
        $routePrefix = $mailshot->type === MailshotTypeEnum::NEWSLETTER ? 'grp.org.shops.show.marketing.newsletters' : 'grp.org.shops.show.marketing.mailshots';
        $routeParameters = [
            'organisation'              => $mailshot->organisation->slug,
            'shop'                      => $mailshot->shop->slug,
            'mailshot'                  => $mailshot->slug,
        ];

        $stats     = $mailshot->stats;
        $sent      = (int)$stats->number_dispatched_emails;
        $delivered = (int)$stats->number_deliveries_success;
        $opened    = (int)$stats->number_delivered_open_success;
        $clicked   = (int)$stats->number_opened_interact_success;
        $bounced   = (int)$stats->number_dispatched_emails_state_hard_bounce + (int)$stats->number_dispatched_emails_state_soft_bounce;
        $ordered   = $this->placedOrders($mailshot, $trackingEndsAt);

        return [
            'label'                     => $label,
            'id'                        => $mailshot->id,
            'subject'                   => $mailshot->subject,
            'sender_name'               => $mailshot->senderName(),
            'state'                     => $mailshot->state->value,
            'state_label'               => MailshotStateEnum::labels()[$mailshot->state->value],
            'route'                     => ['name' => $routePrefix.'.show', 'parameters' => $routeParameters],
            'workshop_route'            => ['name' => $routePrefix.'.workshop', 'parameters' => $routeParameters],
            'copy'                      => [
                'subject'      => $mailshot->subject,
                'name'         => $mailshot->name,
                'preview_text' => $mailshot->preview_text,
                'sender_name'  => $mailshot->sender_name,
            ],
            'update_route'              => [
                'name'       => 'grp.models.shop.mailshot.update',
                'parameters' => ['mailshot' => $mailshot->id],
                'method'     => 'patch',
            ],
            'suggest_copy_route'        => [
                'name'       => 'grp.json.mailshot.copy_suggestion',
                'parameters' => ['mailshot' => $mailshot->id],
                'method'     => 'post',
            ],
            'stats'                     => [
                'sent'             => $sent,
                'delivered'        => $delivered,
                'opened'           => $opened,
                'clicked'          => $clicked,
                'placed_orders'    => $ordered,
                'open_rate'        => $this->rate($opened, $delivered),
                'click_rate'       => $this->rate($clicked, $delivered),
                'placed_order_rate' => $this->rate($ordered, $delivered),
                'spam_rate'        => $this->rate((int)$stats->number_dispatched_emails_state_spam, $sent),
                'unsubscribe_rate' => $this->rate((int)$stats->number_dispatched_emails_state_unsubscribed, $sent),
                'bounce_rate'      => $this->rate($bounced, $sent),
            ],
        ];
    }

    /**
     * Recipients of this version who reached the checkout or purchased on the website from an email visit,
     * after it started sending and up to the end of the tracking window, as in the SEO conversions table.
     */
    private function placedOrders(Mailshot $mailshot, ?Carbon $trackingEndsAt): int
    {
        if (!$mailshot->start_sending_at) {
            return 0;
        }

        return DB::table('mailshot_recipients')
            ->where('mailshot_recipients.mailshot_id', $mailshot->id)
            ->where('mailshot_recipients.recipient_type', 'Customer')
            ->whereExists(fn ($query) => $query->select(DB::raw(1))
                ->from('orders')
                ->join('website_conversion_events', 'website_conversion_events.order_id', '=', 'orders.id')
                ->join('website_visitors', 'website_visitors.id', '=', 'website_conversion_events.website_visitor_id')
                ->whereColumn('orders.customer_id', 'mailshot_recipients.recipient_id')
                ->whereIn('website_conversion_events.event_type', [WebsiteConversionEventTypeEnum::CHECKOUT->value, WebsiteConversionEventTypeEnum::PURCHASE->value])
                ->where('website_visitors.traffic_source_type', WebsiteVisitorChannelEnum::EMAIL_TYPE)
                ->where('website_conversion_events.created_at', '>=', $mailshot->start_sending_at)
                ->when($trackingEndsAt, fn ($query) => $query->where('website_conversion_events.created_at', '<=', $trackingEndsAt)))
            ->count();
    }

    /**
     * The chosen metric decides; null when the two versions are level on it.
     *
     * @param  array<int, array{id: int, stats: array<string, int|float>}>  $versions
     */
    private function suggestedWinnerId(array $versions, MailshotAbTestWinningMetricEnum $winningMetric): ?int
    {
        [$versionA, $versionB] = $versions;

        return match ($versionA['stats'][$winningMetric->value] <=> $versionB['stats'][$winningMetric->value]) {
            1       => $versionA['id'],
            -1      => $versionB['id'],
            default => null,
        };
    }

    private function rate(int $count, int $base): float
    {
        return $base ? round($count / $base * 100, 2) : 0.0;
    }
}
