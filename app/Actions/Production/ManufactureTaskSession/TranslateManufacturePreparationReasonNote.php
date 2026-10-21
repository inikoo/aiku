<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 17:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Actions\Helpers\Translations\DetectLanguage;
use App\Actions\Helpers\Translations\Translate;
use App\Models\Helpers\Language;
use App\Models\Production\ManufactureTaskSession;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The "Other" reason an artisan typed for Preparation, in whatever language they wrote it: the language is detected
 * (the artisan's own as a hint) and an English copy kept, which the Performance tab shows, filters and totals on.
 */
class TranslateManufacturePreparationReasonNote
{
    use AsAction;

    public string $jobQueue = 'translate-chat';
    public int $jobTries = 1;

    public function handle(int $sessionId): void
    {
        $session = ManufactureTaskSession::with('user')->find($sessionId);
        if (!$session?->preparation_reason_note) {
            return;
        }

        $english  = Language::where('code', 'en')->firstOrFail();
        $hint     = $session->user?->language_id ? Language::find($session->user->language_id) : null;
        $language = DetectLanguage::run($session->preparation_reason_note, $hint) ?? $hint ?? $english;

        $session->update([
            'preparation_reason_note_language_id' => $language->id,
            'preparation_reason_note_en'          => Translate::run($session->preparation_reason_note, $language, $english),
        ]);
    }
}
