<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 12 Oct 2026 12:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

namespace App\Actions\Comms\Mailshot\UI;

use App\Enums\Comms\Mailshot\MailshotStateEnum;
use App\Enums\Comms\Mailshot\MailshotTypeEnum;
use App\Models\Comms\Mailshot;
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

        return [
            'enabled'                   => (bool)$variant,
            'is_variant'                => $mailshot->isAbTestVariant(),
            'can_edit_settings'         => $canEdit && !$mailshot->isAbTestVariant() && in_array($original->state, [MailshotStateEnum::IN_PROCESS, MailshotStateEnum::READY]),
            'is_blocked_by_second_wave' => $original->is_second_wave_enabled,
            'size_percentage'           => $original->ab_test_size_percentage,
            'winner_mailshot_id'        => $original->ab_test_winner_mailshot_id,
            'winner_sent_at'            => $original->ab_test_winner_sent_at,
            'can_send_winner'           => $canEdit && $bothSent && $hasRemainingList && !$original->ab_test_winner_sent_at,
            'versions'                  => $variant ? [$this->version('A', $original), $this->version('B', $variant)] : [],
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

    private function version(string $label, Mailshot $mailshot): array
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

        return [
            'label'                     => $label,
            'id'                        => $mailshot->id,
            'subject'                   => $mailshot->subject,
            'sender_name'               => $mailshot->senderName(),
            'state'                     => $mailshot->state->value,
            'state_label'               => MailshotStateEnum::labels()[$mailshot->state->value],
            'route'                     => ['name' => $routePrefix.'.show', 'parameters' => $routeParameters],
            'workshop_route'            => ['name' => $routePrefix.'.workshop', 'parameters' => $routeParameters],
            'stats'                     => [
                'sent'             => $sent,
                'delivered'        => $delivered,
                'opened'           => $opened,
                'clicked'          => $clicked,
                'open_rate'        => $this->rate($opened, $delivered),
                'click_rate'       => $this->rate($clicked, $delivered),
                'spam_rate'        => $this->rate((int)$stats->number_dispatched_emails_state_spam, $sent),
                'unsubscribe_rate' => $this->rate((int)$stats->number_dispatched_emails_state_unsubscribed, $sent),
                'bounce_rate'      => $this->rate($bounced, $sent),
            ],
        ];
    }

    private function rate(int $count, int $base): float
    {
        return $base ? round($count / $base * 100, 2) : 0.0;
    }
}
