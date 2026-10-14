<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\Seo;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithShopPpcPermissions;
use App\Models\SysAdmin\User;
use App\Models\Web\SeoTrackedKeyword;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;

/**
 * Watching a keyword is personal: the watcher is told when it falls in a Google check.
 */
class ToggleSeoKeywordWatch extends OrgAction
{
    use WithShopPpcPermissions;

    public function authorize(ActionRequest $request): bool
    {
        return $this->canViewSeo($request->user(), $this->shop);
    }

    public function handle(SeoTrackedKeyword $trackedKeyword, User $user): bool
    {
        $isWatching = $trackedKeyword->watchers()->where('users.id', $user->id)->exists();

        if ($isWatching) {
            $trackedKeyword->watchers()->detach($user->id);
        } else {
            $trackedKeyword->watchers()->attach($user->id, ['created_at' => now()]);
        }

        return !$isWatching;
    }

    public function asController(SeoTrackedKeyword $seoTrackedKeyword, ActionRequest $request): bool
    {
        $this->initialisationFromShop($seoTrackedKeyword->shop, $request);

        return $this->handle($seoTrackedKeyword, $request->user());
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
