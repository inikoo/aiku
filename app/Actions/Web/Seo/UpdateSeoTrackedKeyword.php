<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 07 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Enums\Web\Seo\SeoKeywordFrequencyEnum;
use App\Models\Web\SeoTrackedKeyword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateSeoTrackedKeyword extends OrgAction
{
    use WithSeoEditAuthorisation;

    /**
     * @param  array{is_active?: bool, frequency?: string, all_countries?: bool}  $modelData
     */
    public function handle(SeoTrackedKeyword $trackedKeyword, array $modelData): SeoTrackedKeyword
    {
        $everyCountry = Arr::pull($modelData, 'all_countries', false);

        ($everyCountry ? $trackedKeyword->onEveryMarket() : $trackedKeyword->onEveryDevice())->update($modelData);

        return $trackedKeyword->refresh();
    }

    public function rules(): array
    {
        return [
            'all_countries' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'frequency' => ['sometimes', Rule::enum(SeoKeywordFrequencyEnum::class)],
        ];
    }

    public function asController(SeoTrackedKeyword $seoTrackedKeyword, ActionRequest $request): SeoTrackedKeyword
    {
        $this->initialisationFromShop($seoTrackedKeyword->shop, $request);

        return $this->handle($seoTrackedKeyword, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
