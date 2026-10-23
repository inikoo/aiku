<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * Stops tracking many keywords at once, in every country, language and device they are tracked in.
 */
class DeleteSeoTrackedKeywords extends OrgAction
{
    use WithSeoEditAuthorisation;

    public const int MAX_KEYWORDS = 1000;

    /**
     * @param  array<int, string>  $keywords
     */
    public function handle(Shop $shop, array $keywords): int
    {
        $trackedKeywords = $shop->seoTrackedKeywords()->whereIn('keyword', $keywords)->get();

        $trackedKeywords->each->delete();

        return $trackedKeywords->pluck('keyword')->unique()->count();
    }

    public function rules(): array
    {
        return [
            'keywords'   => ['required', 'array', 'min:1', 'max:'.self::MAX_KEYWORDS],
            'keywords.*' => ['string', 'max:255'],
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): int
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop, $this->validatedData['keywords']);
    }

    public function htmlResponse(int $removed): RedirectResponse
    {
        return back()->with('notification', [
            'status' => 'success',
            'title'  => __('Stopped tracking :count keywords', ['count' => $removed]),
        ]);
    }
}
