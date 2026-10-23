<?php

/*
 * Author: Andi Ferdiawan <dev@aw-advantage.com>
 * Copyright (c) 2026, Andi Ferdiawan
 */

namespace App\Actions\Chat\ChatSession;

use App\Actions\Chat\WithChatAgentAuthorisation;
use App\Enums\CRM\Livechat\ChatActorTypeEnum;
use App\Enums\CRM\Livechat\ChatAssignmentStatusEnum;
use App\Enums\CRM\Livechat\ChatChannelEnum;
use App\Enums\CRM\Livechat\ChatEventTypeEnum;
use App\Enums\CRM\Livechat\ChatSenderTypeEnum;
use App\Events\BroadcastChatListEvent;
use App\Models\Chat\ChatAgent;
use App\Models\Chat\ChatSession;
use App\Models\CRM\Customer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkChatSessionAsSpam
{
    use AsAction;
    use WithChatAgentAuthorisation;

    /**
     *
     * @throws \Throwable
     */
    public function handle(ChatSession $chatSession, ChatAgent $agent, bool $blockSender = true): ChatSession
    {
        return DB::transaction(function () use ($chatSession, $agent, $blockSender) {
            // Only hide the chat (is_spam). Status and any assignment are left intact
            // so un-marking restores the exact previous state (waiting/active/closed).
            $chatSession->update([
                'is_spam'             => true,
                'spam_at'             => now(),
                'spammed_by_agent_id' => $agent->id,
            ]);

            if ($blockSender && $chatSession->channel === ChatChannelEnum::EMAIL && !self::isFromCustomer($chatSession)) {
                $email = Arr::get($chatSession->metadata, 'email');
                if ($email) {
                    $shop = $chatSession->shop;
                    if ($shop) {
                        $settings = $shop->settings ?? [];
                        $blocked = Arr::get($settings, 'gmail.blocked_senders', []);
                        $blocked[] = strtolower($email);
                        Arr::set($settings, 'gmail.blocked_senders', array_values(array_unique($blocked)));
                        $shop->update(['settings' => $settings]);
                    }
                }
            }

            ClassifyChatSessionNoise::humanDecided($chatSession, true);


            StoreChatEvent::make()->handle(
                chatSession: $chatSession,
                eventType: ChatEventTypeEnum::SPAM,
                actorType: ChatActorTypeEnum::AGENT,
                actorId: $agent->id,
                payload: [
                    'action_type'     => 'spam',
                    'spammed_by_id'   => $agent->id,
                    'spammed_by_name' => $agent->user?->contact_name,
                    'spammed_at'      => now()->toISOString(),
                ]
            );

            BroadcastChatListEvent::dispatch(null, $chatSession);

            return $chatSession->fresh();
        });
    }

    /**
     * Why an assistant may not put this chat in spam, or may not offer it: anything from a
     * customer, and, unless $untouchedOnly is false, a chat somebody holds or already answered.
     */
    public static function refusedForAssistant(ChatSession $chatSession, bool $untouchedOnly = true): ?string
    {
        if ($chatSession->is_spam) {
            return __('This chat is already in spam.');
        }

        if (self::isFromCustomer($chatSession)) {
            return __('This chat is from a customer, so it is never put in spam.');
        }

        if (!$untouchedOnly) {
            return null;
        }

        if ($chatSession->assignments()->where('status', ChatAssignmentStatusEnum::ACTIVE->value)->exists()) {
            return __('Somebody is holding this chat, so it is theirs to decide.');
        }

        if ($chatSession->messages()->where('sender_type', ChatSenderTypeEnum::AGENT->value)->exists()) {
            return __('This chat was already answered, so it is not spam.');
        }

        return null;
    }

    public static function isFromCustomer(ChatSession $chatSession): bool
    {
        if ($chatSession->web_user_id) {
            return true;
        }

        $email = strtolower(trim((string) Arr::get($chatSession->metadata, 'email')));

        return $email !== '' && Customer::where('shop_id', $chatSession->shop_id)->whereRaw('lower(email) = ?', [$email])->exists();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function asController(?string $organisation, ChatSession $chatSession): JsonResponse
    {
        $agent = $this->getCurrentAgent($chatSession);

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => 'Only authenticated agents can mark chats as spam',
            ], 403);
        }

        if (!$this->userCanDisposeOfChat($agent->user, $chatSession)) {
            return response()->json([
                'success' => false,
                'message' => $this->chatHeldByAnotherAgentMessage($chatSession),
            ], 403);
        }

        if ($chatSession->is_spam) {
            return response()->json([
                'success' => false,
                'message' => 'Chat session is already marked as spam',
            ], 422);
        }

        try {
            $chatSession = $this->handle($chatSession, $agent);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chat marked as spam',
            'data'    => [
                'session_ulid' => $chatSession->ulid,
                'is_spam'      => true,
                'action_type'  => 'spam',
            ],
        ]);
    }

    public function getCurrentAgent(ChatSession $chatSession): ?ChatAgent
    {
        return $this->getAuthorisedChatAgent($chatSession);
    }
}
