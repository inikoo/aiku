<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Enums\CRM\Livechat\ChatAiDraftStatusEnum;
use App\Enums\CRM\Livechat\ChatSenderTypeEnum;
use App\Enums\CRM\Livechat\ChatTopicEnum;
use App\Events\BroadcastChatAiDraft;
use App\Models\Chat\ChatAiDraft;
use App\Models\Chat\ChatSession;
use App\Models\SysAdmin\User;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A reply the agent's own assistant wrote, saved on the conversation for the agent to use, edit or
 * discard in the draft box like any other draft. It replaces the pending draft, is never sent by
 * itself, and only its id is broadcast: the website chat channel is public.
 */
class StoreAssistantChatDraft
{
    use AsAction;

    public function handle(ChatSession $chatSession, User $user, string $text, ?string $client): ChatAiDraft
    {
        $shop    = $chatSession->shop;
        $trigger = $chatSession->messages()->whereIn('sender_type', [ChatSenderTypeEnum::GUEST, ChatSenderTypeEnum::USER])->latest('id')->first();

        return DB::transaction(function () use ($chatSession, $shop, $user, $text, $client, $trigger) {
            DraftChatReply::pendingDraft($chatSession)?->update(['status' => ChatAiDraftStatusEnum::SUPERSEDED, 'decided_at' => now()]);

            $draft = ChatAiDraft::create([
                'group_id'           => $shop->group_id,
                'organisation_id'    => $shop->organisation_id,
                'shop_id'            => $shop->id,
                'chat_session_id'    => $chatSession->id,
                'trigger_message_id' => $trigger?->id,
                'topic'              => ChatTopicEnum::tryFrom((string) $chatSession->topic) ?? ChatTopicEnum::OTHER,
                'facts'              => [
                    'mode'    => DraftChatReply::ASSISTANT,
                    'client'  => $client,
                    'user_id' => $user->id,
                ],
                'text'               => $text,
                'status'             => ChatAiDraftStatusEnum::PENDING,
            ]);

            DB::afterCommit(fn () => BroadcastChatAiDraft::dispatch($chatSession, $draft));

            return $draft;
        });
    }
}
