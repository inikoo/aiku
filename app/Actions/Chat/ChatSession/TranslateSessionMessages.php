<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jun 2026 21:08:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Models\SysAdmin\User;
use App\Models\Catalogue\Shop;
use App\Actions\Chat\WithChatAgentAuthorisation;
use App\Enums\CRM\Livechat\ChatSenderTypeEnum;
use App\Events\TranslationChatIndicator;
use App\Models\Chat\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class TranslateSessionMessages
{
    use AsAction;
    use WithChatAgentAuthorisation;

    public function authorize(ActionRequest $request): bool
    {
        if (!$request->user() instanceof User) {
            return false;
        }

        $shop = $request->route('chatSession')?->shop;

        return $shop instanceof Shop && $request->user() instanceof User && $this->userCanActOnChatOnShop($request->user(), $shop);
    }

    public function handle(ChatSession $chatSession, int $targetLanguageId): void
    {
        $messages = $chatSession->messages()
            ->whereIn('sender_type', [ChatSenderTypeEnum::USER, ChatSenderTypeEnum::GUEST])
            ->whereDoesntHave('translations', function ($query) use ($targetLanguageId) {
                $query->where('target_language_id', $targetLanguageId);
            })
            ->get();

        if ($messages->isEmpty()) {
            TranslationChatIndicator::dispatch($chatSession, $targetLanguageId);
        }

        $jobs = [];

        foreach ($messages as $message) {
            $jobs[] = TranslateChatMessage::makeJob(
                $message->id,
                $targetLanguageId,
                'translate-session'
            );
        }


        $chatSessionId = $chatSession->id;

        $jobs[] = function () use ($chatSessionId, $targetLanguageId) {


            $session = ChatSession::find($chatSessionId);

            if ($session) {
                TranslationChatIndicator::dispatch($session, $targetLanguageId);
            }
        };

        Bus::chain($jobs)->dispatch();
    }

    public function asController(ActionRequest $request, ChatSession $chatSession): JsonResponse
    {
        $validatedData = $request->validated();

        $this->handle($chatSession, $validatedData['target_language_id']);

        return response()->json([
            'success' => true,
            'message' => 'Session messages translation processed successfully',
        ]);
    }

    public function rules(): array
    {
        return [
            'target_language_id' => 'required|exists:languages,id',
        ];
    }
}
