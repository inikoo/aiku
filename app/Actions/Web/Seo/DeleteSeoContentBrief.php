<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Web\SeoContentBrief;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class DeleteSeoContentBrief extends OrgAction
{
    use WithSeoEditAuthorisation;

    public function handle(SeoContentBrief $brief): void
    {
        $brief->delete();
    }

    public function asController(SeoContentBrief $seoContentBrief, ActionRequest $request): void
    {
        $this->initialisationFromShop($seoContentBrief->shop, $request);

        $this->handle($seoContentBrief);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
