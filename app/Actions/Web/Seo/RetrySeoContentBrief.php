<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Enums\Web\Seo\SeoContentBriefStateEnum;
use App\Models\Web\SeoContentBrief;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * Writes a brief again, for one that failed or to read Google afresh. It reads Google and the AI
 * gateway once more, so it costs the same as a new brief.
 */
class RetrySeoContentBrief extends OrgAction
{
    use WithSeoEditAuthorisation;

    public function handle(SeoContentBrief $brief): SeoContentBrief
    {
        if ($brief->isBeingWritten()) {
            return $brief;
        }

        $brief->update([
            'state'       => SeoContentBriefStateEnum::PENDING,
            'error'       => null,
            'finished_at' => null,
            'created_at'  => now(),
        ]);

        GenerateSeoContentBrief::dispatch($brief);

        return $brief;
    }

    public function asController(SeoContentBrief $seoContentBrief, ActionRequest $request): SeoContentBrief
    {
        $this->initialisationFromShop($seoContentBrief->shop, $request);

        return $this->handle($seoContentBrief);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
