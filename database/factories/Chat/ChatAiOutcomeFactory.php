<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace Database\Factories\Chat;

use App\Enums\CRM\Livechat\ChatAiOutcomeEnum;
use App\Models\Chat\ChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatAiOutcomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'outcome' => ChatAiOutcomeEnum::SPAM,
            'reason'  => fake()->sentence(4),
        ];
    }

    public function forChatSession(ChatSession $chatSession, ?int $triggerMessageId = null): static
    {
        return $this->state(fn () => [
            'group_id'           => $chatSession->shop->group_id,
            'organisation_id'    => $chatSession->shop->organisation_id,
            'shop_id'            => $chatSession->shop_id,
            'chat_session_id'    => $chatSession->id,
            'channel'            => $chatSession->channel->value,
            'trigger_message_id' => $triggerMessageId,
        ]);
    }
}
