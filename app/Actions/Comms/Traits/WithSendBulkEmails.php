<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 14 Dec 2023 13:11:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Traits;

use App\Actions\Comms\Ses\SendSesEmail;
use App\Models\Comms\DispatchedEmail;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use App\Models\CRM\WebUser;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait WithSendBulkEmails
{
    public function sendEmailWithMergeTags(DispatchedEmail $dispatchedEmail, string $sender, string $subject, string $emailHtmlBody, string $unsubscribeUrl = null, string $passwordToken = null, string $invoiceUrl = null, array $additionalData = []): DispatchedEmail
    {
        $html = $emailHtmlBody;

        $html = $this->processStyles($html);


        if (preg_match_all("/{{(.*?)}}/", $html, $matches)) {
            foreach ($matches[1] as $i => $placeholder) {
                $placeholder = $this->replaceMergeTags($placeholder, $dispatchedEmail, $unsubscribeUrl, $passwordToken, $invoiceUrl, $additionalData);
                $html        = str_replace($matches[0][$i], sprintf('%s', $placeholder), $html);
            }
        }
        if (preg_match_all("/\[(.*?)]/", $html, $matches)) {
            foreach ($matches[1] as $i => $tag) {
                $placeholder = $this->replaceMergeTags($tag, $dispatchedEmail, $unsubscribeUrl, $passwordToken, $invoiceUrl, $additionalData);
                $html        = str_replace($matches[0][$i], sprintf('%s', $placeholder), $html);
            }
        }

        return SendSesEmail::run(
            subject: $subject,
            emailHtmlBody: $html,
            dispatchedEmail: $dispatchedEmail,
            sender: $sender,
            unsubscribeUrl: $unsubscribeUrl
        );
    }

    private function replaceMergeTags($placeholder, $dispatchedEmail, $unsubscribeUrl = null, $passwordToken = null, $invoiceUrl = null, array $additionalData = []): ?string
    {
        $originalPlaceholder = $placeholder;
        $placeholder = Str::kebab(trim($placeholder));

        if ($dispatchedEmail->recipient instanceof WebUser) {
            $customerName = $dispatchedEmail->recipient->customer->name;
        } else {
            $customerName = $dispatchedEmail->recipient->name;
        }

        return match ($placeholder) {
            'username' => $this->getUsername($dispatchedEmail->recipient),
            'customer-name' => $customerName,
            'rejected-notes' => Arr::get($additionalData, 'rejected_notes'),
            'invoice_-url' => $invoiceUrl,
            'reset_-password_-u-r-l' => $passwordToken,
            'unsubscribe' => sprintf(
                "<a ses:no-track href=\"$unsubscribeUrl\">%s</a>",
                __('Unsubscribe')
            ),
            default => $originalPlaceholder,
        };
    }

    public function getUsername(WebUser|Customer|Prospect|User $recipient): string
    {
        if ($recipient instanceof WebUser || $recipient instanceof User) {
            return $recipient->username;
        }

        return '';
    }

    public function getName(WebUser|Customer|Prospect|User $recipient): string
    {
        if ($recipient instanceof WebUser) {
            return $recipient->customer->name;
        } elseif ($recipient instanceof Customer || $recipient instanceof Prospect) {
            return $recipient->name;
        } else {
            return $recipient->company_name ?? $recipient->username;
        }


    }


    public function processStyles($html): array|string|null
    {
        $html = preg_replace_callback('/<[^>]+style=["\'](.*?)["\'][^>]*>/i', function ($match) {
            $style = $match[1];

            // Find and modify color values within the style attribute
            $style = preg_replace_callback('/color\s*:\s*([^;]+);/i', function ($colorMatch) {
                $colorValue    = $colorMatch[1];
                $modifiedColor = $colorValue.' !important';

                return 'color: '.$modifiedColor.';';
            }, $style);

            // Update the style attribute in the HTML tag
            return str_replace($match[1], $style, $match[0]);
        }, $html);

        // Remove <style> tags and their content
        return preg_replace('/<style(.*?)>(.*?)<\/style>/is', '', $html);
    }
}
