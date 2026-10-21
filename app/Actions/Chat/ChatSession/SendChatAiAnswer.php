<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 24 Sep 2026 04:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Actions\Chat\MetaChatSession\SendMetaChatGreeting;
use App\Actions\Chat\Reports\IsWithinWorkingHours;
use App\Enums\CRM\Livechat\ChatAiDraftStatusEnum;
use App\Enums\CRM\Livechat\ChatMessageTypeEnum;
use App\Enums\CRM\Livechat\ChatSenderTypeEnum;
use App\Models\Chat\ChatAiDraft;
use App\Models\Chat\ChatSession;
use App\Models\Chat\MetaChatSession;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Sends a draft to the customer without a person, when nobody is there to read it and drafts on
 * its topic in its shop have earned it. It always says it is automatic and never goes out in
 * working hours. Emails get no quick draft, so none goes by email. The draft is kept as sent
 * automatically so staff can see it in the morning and flag it if it was wrong, which closes
 * the gate for that topic and shop.
 */
class SendChatAiAnswer
{
    use AsAction;

    public const string SENT_KEY = 'ai_answered_at';

    /** The message is listed in the AI tab through its draft, which carries what became of it. */
    public const string MESSAGE_MARKER = 'ai_answer';

    public function handle(ChatAiDraft $draft): bool
    {
        $session = $draft->session();
        $shop    = $draft->shop;

        if (!config('chat.ai_auto_send.enabled')
            || !$session
            || $draft->status !== ChatAiDraftStatusEnum::PENDING
            || data_get($draft->facts, 'mode') === DraftChatReply::SUGGESTION
            || IsWithinWorkingHours::make()->chatHours()->handle($shop, now())
            || !GetChatAutoSendGate::run($shop, $draft->topic)['earned']) {
            return false;
        }

        $locale = $shop->language?->code;
        $text   = $draft->text."\n\n".__('This is an automatic reply from our system. A member of the team will follow up if you need anything else.', [], $locale);

        if ($session instanceof MetaChatSession) {
            $sent = SendMetaChatGreeting::run($session, $text, self::SENT_KEY, false);
            $replyId = $sent ? $session->messages()->latest('id')->value('id') : null;
        } else {
            $replyId = $this->sendInChat($session, $text);
        }

        if (!$replyId) {
            return false;
        }

        $session->update([
            'metadata' => array_merge($session->metadata ?? [], [
                SendOutOfHoursReply::SENT_KEY => now()->toISOString(),
            ]),
        ]);

        $draft->update([
            'status'           => ChatAiDraftStatusEnum::AUTO_SENT,
            'reply_message_id' => $replyId,
            'decided_at'       => now(),
        ]);

        return true;
    }

    private function sendInChat(ChatSession $session, string $text): int
    {
        $reply = SendChatMessage::run($session, [
            'message_text' => $text,
            'message_type' => ChatMessageTypeEnum::TEXT->value,
            'sender_type'  => ChatSenderTypeEnum::SYSTEM->value,
        ]);

        $reply->update(['metadata' => array_merge($reply->metadata ?? [], ['automated' => self::MESSAGE_MARKER])]);

        return $reply->id;
    }
}
