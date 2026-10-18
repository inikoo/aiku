<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\Web\SeoTrackedKeyword;
use App\Services\DataForSeo\DataForSeoException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

/**
 * Check now: queues a Google check for the shop's active tracked keywords, or for the ones chosen,
 * without waiting for the schedule. The results are collected by the scheduler within minutes to an
 * hour. Each check is billed and counts against the SEO API budget.
 */
class CheckSeoKeywordsNow extends OrgAction
{
    use WithSeoEditAuthorisation;

    /**
     * @param  array<int, int>|null  $trackedKeywordIds
     * @throws ValidationException
     */
    public function handle(Shop $shop, ?array $trackedKeywordIds = null): int
    {
        try {
            return PostSerpTasks::make()->requested($shop, $trackedKeywordIds);
        } catch (DataForSeoException $e) {
            throw ValidationException::withMessages(['checks' => $e->getMessage()]);
        }
    }

    public function rules(): array
    {
        return [
            'tracked_keyword_ids'   => ['sometimes', 'array', 'min:1'],
            'tracked_keyword_ids.*' => ['integer', Rule::exists(SeoTrackedKeyword::class, 'id')->where('shop_id', $this->shop->id)],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function asController(Shop $shop, ActionRequest $request): int
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData['tracked_keyword_ids'] ?? null);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
