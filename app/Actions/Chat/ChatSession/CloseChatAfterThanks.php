<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 28 Sep 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Actions\Chat\MetaChatSession\CloseMetaChatSession;
use App\Actions\Chat\MetaChatSession\StoreMetaChatMessage;
use App\Actions\Chat\UpdateShopChatClosing;
use App\Actions\Chat\WithBlockingTickets;
use App\Actions\Chat\Whatsapp\SendWhatsappReaction;
use App\Enums\CRM\Livechat\ChatActorTypeEnum;
use App\Enums\CRM\Livechat\ChatAssignmentStatusEnum;
use App\Enums\CRM\Livechat\ChatAutomationKindEnum;
use App\Enums\CRM\Livechat\ChatChannelEnum;
use App\Enums\CRM\Livechat\ChatMessageTypeEnum;
use App\Enums\CRM\Livechat\ChatSenderTypeEnum;
use App\Enums\CRM\Livechat\ChatSessionStatusEnum;
use App\Events\BroadcastChatClosingAfterThanks;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatSession;
use App\Models\Chat\MetaChatMessage;
use App\Models\Chat\MetaChatSession;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A customer who only thanked us gets a 👍 on website chat and WhatsApp and the conversation
 * closes; an email is left waiting for the customer for the shop's hours, nothing sent. When an agent is in the chat it waits the
 * shop's minutes instead, with a countdown on their screen, and closes only if nobody has
 * written since the thanks and the agent did not start typing or click "Keep open".
 */
class CloseChatAfterThanks
{
    use AsAction;
    use WithBlockingTickets;

    public int $jobTimeout = 60;
    public int $jobTries = 1;

    public const string REACTION = '👍';

    public const string PENDING_KEY = 'thanks_close';

    private const array WRITERS = [ChatSenderTypeEnum::AGENT, ChatSenderTypeEnum::USER, ChatSenderTypeEnum::GUEST];

    /**
     * An agent with the conversation open marks each customer message read as it arrives, so
     * a thanks already read means somebody is in the chat.
     * ponytail: read receipt as presence; an agent who left the chat open and walked away still counts as there.
     */
    public static function closeNowOrLater(ChatSession|MetaChatSession $chatSession): void
    {
        $thanks = $chatSession->messages()
            ->whereIn('sender_type', [ChatSenderTypeEnum::USER->value, ChatSenderTypeEnum::GUEST->value])
            ->latest('id')
            ->first();

        if (!$thanks) {
            return;
        }

        if ($chatSession instanceof ChatSession && $chatSession->channel === ChatChannelEnum::EMAIL) {
            WaitForCustomerReply::run($chatSession, UpdateShopChatClosing::waitingHours($chatSession->shop), 'thanks');

            return;
        }

        if ($thanks->is_read) {
            $closingAt = now()->addMinutes(UpdateShopChatClosing::minutes($chatSession->shop));

            SetChatSessionMetadata::run($chatSession, [self::PENDING_KEY => ['message_id' => $thanks->id, 'at' => $closingAt->toISOString()]]);
            BroadcastChatClosingAfterThanks::dispatch($chatSession, $closingAt->toISOString());
            static::dispatch($chatSession, $thanks->id, true)->delay($closingAt);

            return;
        }

        static::run($chatSession, $thanks->id);
    }

    /**
     * The thanks on an email chat an assistant may close as done, or why not: it is the customer's
     * latest message, it follows a reply from us, it is short, asks nothing and names no order, the
     * chat waits on no ticket or task, and, unless $untouchedOnly is false, nobody holds the chat.
     * ponytail: length, a question mark or an order-like token refuses; a false refusal only leaves the chat for staff.
     *
     * @return array{0: ChatMessage|null, 1: string|null}
     */
    public static function thanksToClose(ChatSession $chatSession, bool $untouchedOnly = true): array
    {
        if ($chatSession->channel !== ChatChannelEnum::EMAIL) {
            return [null, __('Only an email chat is closed this way.')];
        }

        if ($chatSession->status === ChatSessionStatusEnum::CLOSED) {
            return [null, __('This chat is already closed.')];
        }

        $blockers = self::make()->unresolvedBlockers($chatSession);
        if ($blockers->isNotEmpty()) {
            return [null, self::make()->blockersMessage($blockers)];
        }

        if ($untouchedOnly && $chatSession->assignments()->where('status', ChatAssignmentStatusEnum::ACTIVE->value)->exists()) {
            return [null, __('Somebody is holding this chat, so it is theirs to close.')];
        }

        [$thanks, $previous] = array_pad($chatSession->messages()
            ->whereIn('sender_type', array_map(fn (ChatSenderTypeEnum $sender) => $sender->value, self::WRITERS))
            ->latest('id')
            ->limit(2)
            ->get()
            ->all(), 2, null);

        if (!$thanks || $thanks->sender_type === ChatSenderTypeEnum::AGENT) {
            return [null, __('The customer has not written since our last reply.')];
        }

        if ($previous?->sender_type !== ChatSenderTypeEnum::AGENT) {
            return [null, __('The customer\'s last message does not follow a reply from us.')];
        }

        $text = trim((string) $thanks->message_text);
        if (mb_strlen($text) > 120 || preg_match('/[?？¿]/u', $text) || preg_match('/\b[A-Z]{2,}[-\/]?\d{3,}\b|\d{5,}/', $text)) {
            return [null, __('The customer\'s last message may ask for something, so it is left for staff.')];
        }

        return [$thanks, null];
    }

    /**
     * Closes as done, the way a website chat closes after a thanks, an email the assistant read as
     * only a thanks. Nothing is sent to the customer.
     *
     * @throws ValidationException
     */
    public static function closeForAssistant(ChatSession $chatSession, bool $untouchedOnly = true): void
    {
        [$thanks, $refused] = self::thanksToClose($chatSession, $untouchedOnly);

        if ($refused) {
            throw ValidationException::withMessages(['chat_id' => $refused]);
        }

        static::run($chatSession, $thanks->id);

        if ($chatSession->refresh()->status !== ChatSessionStatusEnum::CLOSED) {
            throw ValidationException::withMessages(['chat_id' => __('The chat could not be closed.')]);
        }
    }

    public static function pendingAt(ChatSession|MetaChatSession $chatSession): ?string
    {
        return $chatSession->status === ChatSessionStatusEnum::CLOSED ? null : data_get($chatSession->metadata, self::PENDING_KEY.'.at');
    }

    public static function cancel(ChatSession|MetaChatSession $chatSession): void
    {
        $chatSession->refresh();

        if (!array_key_exists(self::PENDING_KEY, $chatSession->metadata ?? [])) {
            return;
        }

        SetChatSessionMetadata::run($chatSession, [self::PENDING_KEY => null]);
        BroadcastChatClosingAfterThanks::dispatch($chatSession, null);
    }

    public function handle(ChatSession|MetaChatSession $chatSession, int $thanksId, bool $scheduled = false): void
    {
        $chatSession->refresh();

        if ($scheduled && data_get($chatSession->metadata, self::PENDING_KEY.'.message_id') !== $thanksId) {
            return;
        }

        if ($chatSession->status === ChatSessionStatusEnum::CLOSED || $this->writtenSince($chatSession, $thanksId)) {
            self::cancel($chatSession);

            return;
        }

        self::cancel($chatSession);

        $reacted = $this->react($chatSession, $thanksId);

        $note = [
            'message_text' => $reacted
                ? 'Closed automatically: the customer only thanked us, so we answered with a 👍. Anything they write next reopens it.'
                : 'Closed automatically: the customer only thanked us. Anything they write next reopens it.',
            'message_type' => ChatMessageTypeEnum::TEXT->value,
            'sender_type'  => ChatSenderTypeEnum::SYSTEM->value,
            'is_read'      => true,
            'read_at'      => now(),
            'delivered_at' => now(),
            'metadata'     => ['automated' => ChatAutomationKindEnum::THANKS_CLOSED->value],
        ];

        if ($chatSession instanceof MetaChatSession) {
            CloseMetaChatSession::run($chatSession, null, ChatActorTypeEnum::SYSTEM, ['reason' => 'only_thanks']);
            StoreMetaChatMessage::run($chatSession, $note);

            return;
        }

        CloseChatSession::run($chatSession, null, ChatActorTypeEnum::SYSTEM, ['reason' => 'only_thanks']);
        $chatSession->messages()->create($note);
    }

    private function writtenSince(ChatSession|MetaChatSession $chatSession, int $thanksId): bool
    {
        return $chatSession->messages()
            ->where('id', '>', $thanksId)
            ->whereIn('sender_type', array_map(fn (ChatSenderTypeEnum $sender) => $sender->value, self::WRITERS))
            ->exists();
    }

    private function react(ChatSession|MetaChatSession $chatSession, int $thanksId): bool
    {
        if ($chatSession instanceof ChatSession && $chatSession->channel === ChatChannelEnum::EMAIL) {
            return false;
        }

        $thanks = $chatSession->messages()->find($thanksId);

        if ($thanks instanceof MetaChatMessage) {
            return SendWhatsappReaction::run($thanks, null, self::REACTION)['ok'];
        }

        if ($thanks instanceof ChatMessage) {
            ToggleChatMessageReaction::run($thanks, ['reactor_type' => ChatSenderTypeEnum::AGENT->value, 'reactor_id' => null], self::REACTION);

            return true;
        }

        return false;
    }
}
