<?php

/*
 * Author: Andi Ferdiawan <dev@aw-advantage.com>
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Actions\Chat\WithChatAgentAuthorisation;
use App\Enums\CRM\Livechat\ChatSenderTypeEnum;
use App\Events\BroadcastChatListEvent;
use App\Events\BroadcastMetaChatListEvent;
use App\Models\Chat\ChatSession;
use App\Models\Chat\MetaChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * An agent who opened a conversation they cannot deal with yet puts it back as unread, for
 * themselves and their colleagues. What goes back is what the customer is still waiting on:
 * everything they wrote since our last reply, or their last message when we answered it all.
 * Only the agents' lists are told. The customer's side hears nothing, and our own messages
 * keep the read state the customer gave them.
 */
class MarkChatSessionAsUnread
{
    use AsAction;
    use WithChatAgentAuthorisation;

    private const array VISITOR_SENDER_TYPES = [
        ChatSenderTypeEnum::GUEST->value,
        ChatSenderTypeEnum::USER->value,
    ];

    private const array REPLY_SENDER_TYPES = [
        ChatSenderTypeEnum::AGENT->value,
        ChatSenderTypeEnum::AI->value,
    ];

    /**
     * @return int the number of customer messages flagged, zero when the customer never wrote
     */
    public function handle(ChatSession|MetaChatSession $chatSession): int
    {
        $messageIds = $this->unansweredVisitorMessageIds($chatSession);

        if ($messageIds->isEmpty()) {
            $messageIds = $this->lastVisitorMessageId($chatSession);
        }

        if ($messageIds->isEmpty()) {
            return 0;
        }

        $chatSession->messages()
            ->whereIn('id', $messageIds)
            ->where('is_read', true)
            ->update(['is_read' => false, 'read_at' => null]);

        if ($chatSession instanceof MetaChatSession) {
            BroadcastMetaChatListEvent::dispatch(null, $chatSession);
        } else {
            BroadcastChatListEvent::dispatch(null, $chatSession);
        }

        return $messageIds->count();
    }

    /**
     * @return Collection<int, int>
     */
    private function unansweredVisitorMessageIds(ChatSession|MetaChatSession $chatSession): Collection
    {
        $lastReply = $chatSession->messages()
            ->whereIn('sender_type', self::REPLY_SENDER_TYPES)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        return $chatSession->messages()
            ->whereIn('sender_type', self::VISITOR_SENDER_TYPES)
            ->when($lastReply, function ($query) use ($lastReply) {
                $query->where(function ($after) use ($lastReply) {
                    $after->where('created_at', '>', $lastReply->created_at)
                        ->orWhere(function ($sameMoment) use ($lastReply) {
                            $sameMoment->where('created_at', $lastReply->created_at)
                                ->where('id', '>', $lastReply->id);
                        });
                });
            })
            ->pluck('id');
    }

    /**
     * @return Collection<int, int>
     */
    private function lastVisitorMessageId(ChatSession|MetaChatSession $chatSession): Collection
    {
        return $chatSession->messages()
            ->whereIn('sender_type', self::VISITOR_SENDER_TYPES)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(1)
            ->pluck('id');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function asController(?string $organisation, ChatSession $chatSession): JsonResponse
    {
        return $this->respond($chatSession);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWhatsapp(?string $organisation, MetaChatSession $metaChatSession): JsonResponse
    {
        return $this->respond($metaChatSession);
    }

    private function respond(ChatSession|MetaChatSession $chatSession): JsonResponse
    {
        if (!$this->getAuthorisedChatAgent($chatSession)) {
            return response()->json([
                'success' => false,
                'message' => __('Only agents working this shop can mark its chats as unread'),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'session_ulid' => $chatSession->ulid,
                'unread_count' => $this->handle($chatSession),
            ],
        ]);
    }
}
