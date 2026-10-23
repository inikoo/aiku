<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 15:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Chat;

use App\Enums\CRM\Livechat\ChatAiOutcomeEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What the AI customer service sweep did with one customer message, so every chat that came in
 * is counted once in the coverage report: a suggestion, a note, or the reason it was left alone.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int|null $chat_session_id
 * @property int|null $meta_chat_session_id
 * @property string $channel
 * @property ChatAiOutcomeEnum $outcome
 * @property string|null $reason
 * @property int|null $chat_ai_draft_id
 * @property int|null $note_event_id
 * @property int|null $trigger_message_id
 * @property int|null $run_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read ChatSession|null $chatSession
 * @property-read MetaChatSession|null $metaChatSession
 * @property-read ChatAiRun|null $run
 * @property-read Shop $shop
 */
class ChatAiOutcome extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'outcome' => ChatAiOutcomeEnum::class,
        ];
    }

    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function metaChatSession(): BelongsTo
    {
        return $this->belongsTo(MetaChatSession::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ChatAiRun::class, 'run_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
