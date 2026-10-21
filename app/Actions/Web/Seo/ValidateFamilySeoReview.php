<?php

/*
 * Created: Fri, 16 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Enums\Web\Seo\SeoContentSuggestionStateEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\SysAdmin\User;
use App\Models\Web\SeoContentSuggestion;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * The webmaster signs off the SEO review, which completes the family page workflow. Suggestions
 * still pending were confirmed as skipped in the UI, so they are dismissed instead of blocking
 * the validation.
 */
class ValidateFamilySeoReview extends OrgAction
{
    private ProductCategory $family;

    public function handle(ProductCategory $family, ?User $user = null): int
    {
        $suggestions = fn () => SeoContentSuggestion::where('webpage_id', $family->webpage->id)->where('reason', SeoContentSuggestion::REASON_SEO_REVIEW);

        $suggestions()->where('state', SeoContentSuggestionStateEnum::PENDING)->update([
            'state'              => SeoContentSuggestionStateEnum::DISMISSED,
            'decided_by_user_id' => $user?->id,
            'decided_at'         => now(),
        ]);

        $acceptedCount = $suggestions()->where('state', SeoContentSuggestionStateEnum::IMPLEMENTED)->get()
            ->each(fn (SeoContentSuggestion $suggestion) => $suggestion->update(['state' => SeoContentSuggestionStateEnum::ACCEPTED]))
            ->count();
        AskWebmastersToImplementSeoReview::make()->close($family->webpage);

        return $acceptedCount;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return ImplementFamilySeoSuggestion::isWebmaster($request->user(), $this->family);
    }

    public function asController(ProductCategory $productCategory, ActionRequest $request): int
    {
        abort_unless($productCategory->webpage, 404);

        $this->family = $productCategory;
        $this->initialisationFromShop($productCategory->shop, $request);

        return $this->handle($productCategory, $request->user());
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
