<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Web\SeoKeywordTag;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * Removes the tag from every keyword. The keywords, and their checks, stay tracked.
 */
class DeleteSeoKeywordTag extends OrgAction
{
    use WithSeoEditAuthorisation;

    public function handle(SeoKeywordTag $tag): void
    {
        $tag->delete();
    }

    public function asController(SeoKeywordTag $seoKeywordTag, ActionRequest $request): void
    {
        $this->initialisationFromShop($seoKeywordTag->shop, $request);

        $this->handle($seoKeywordTag);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
