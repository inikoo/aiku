<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 24 Sep 2026 04:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

/**
 * Drafts used to be sent to the customer without a person, out of hours, once their topic had
 * earned it. Since INI-128 aiku writes no draft itself, so nothing is sent this way any more;
 * the markers stay for the messages already sent, which staff can still flag.
 */
class SendChatAiAnswer
{
    public const string SENT_KEY = 'ai_answered_at';

    /** The message is listed in the AI tab through its draft, which carries what became of it. */
    public const string MESSAGE_MARKER = 'ai_answer';
}
