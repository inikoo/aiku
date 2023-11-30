<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 May 2023 10:34:26 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI;

use App\Actions\Assets\Language\UI\GetLanguagesOptions;
use App\Http\Resources\Assets\LanguageResource;
use App\Models\Assets\Language;
use App\Models\Auth\User;
use Cache;
use Illuminate\Support\Facades\App;
use Lorisleiva\Actions\Concerns\AsObject;

class GetFirstLoadProps
{
    use AsObject;

    public function handle(?User $user): array
    {
        if ($user) {
            $language = $user->language;
        } else {
            $language = Language::where('code', App::currentLocale())->first();
        }
        if (!$language) {
            $language = Language::where('code', 'en')->first();
        }

        $auth = app('firebase.auth');

        if($user) {
            $customTokenFirebasePrefix = 'org_user_' . $user->username . '_token_' . $user->id;
            $cache                     = Cache::get($customTokenFirebasePrefix);

            if(blank($cache)) {
                $customToken = $auth->createCustomToken($customTokenFirebasePrefix);
                $auth->signInWithCustomToken($customToken);
                Cache::put($customTokenFirebasePrefix, $customToken->toString(), 3600);
            }
        }

        return [
            'localeData' =>
                [
                    'language'        => LanguageResource::make($language)->getArray(),
                    'languageOptions' => GetLanguagesOptions::make()->translated(),
                ],

            'layoutCurrentShopSlug' => function () use ($user) {
                if ($user) {
                    return GetCurrentShopSlug::run($user);
                } else {
                    return null;
                }
            },


            'layoutShopsList' => function () use ($user) {
                if ($user) {
                    return GetShops::run($user);
                } else {
                    return [];
                }
            },
            'layoutWebsitesList' => function () use ($user) {
                if ($user) {
                    return GetWebsites::run($user);
                } else {
                    return [];
                }
            },
            'layoutWarehousesList' => function () use ($user) {
                if ($user) {
                    return GetWarehouses::run($user);
                } else {
                    return [];
                }
            },

            'layout' => function () use ($user) {
                if ($user) {
                    return GetLayout::run($user);
                } else {
                    return [];
                }
            }
        ];
    }
}
