<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Oct 2026 22:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Actions\Helpers\AI\AskJev;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * How close a reply drafted for staff came to what our agent really sent, read by Jev.
 */
class JudgeChatSuggestion
{
    use AsAction;

    public const array NEAR_MATCH = ['different', 'partly the same', 'same answer in other words', 'nearly the same'];

    /**
     * How close a suggestion came to what our agent really sent: the stat that says whether
     * suggestions save typing, counted even when the agent typed their own.
     *
     * @return array{score: int, label: string}|null
     */
    public function nearMatch(string $text, string $staffReply, string $suggestion): ?array
    {
        $answer = AskJev::make()->score(
            ['customer_wrote' => mb_substr($text, 0, 3000), 'agent_sent' => mb_substr($staffReply, 0, 2000), 'suggestion' => mb_substr($suggestion, 0, 2000)],
            'Customer service of a wholesale giftware supplier. How close is "suggestion" to what our agent really sent, "agent_sent"? Judge what it says and does, not the wording or language; [[gaps]] count as left for the agent.',
            self::NEAR_MATCH
        );

        if (!$answer) {
            return null;
        }

        $score = (int) round((float) $answer['score']);

        return ['score' => $score, 'label' => self::NEAR_MATCH[$score] ?? 'different'];
    }
}
