<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 07 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Web\SeoTrackedKeyword;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

class DeleteSeoTrackedKeyword extends OrgAction
{
    use WithSeoEditAuthorisation;

    public function handle(SeoTrackedKeyword $trackedKeyword, bool $everyCountry = false): void
    {
        ($everyCountry ? $trackedKeyword->onEveryMarket() : $trackedKeyword->onEveryDevice())->get()->each->delete();
    }

    public function rules(): array
    {
        return [
            'all_countries' => ['sometimes', 'boolean'],
        ];
    }

    public function asController(SeoTrackedKeyword $seoTrackedKeyword, ActionRequest $request): void
    {
        $this->initialisationFromShop($seoTrackedKeyword->shop, $request);

        $this->handle($seoTrackedKeyword, (bool) ($this->validatedData['all_countries'] ?? false));
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
