<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 01:57:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Actions\Assets\Language\UI\GetLanguagesOptions;
use App\Actions\UI\GetLayout;
use App\Http\Resources\Assets\LanguageResource;
use App\Models\Assets\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleCentralInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';


    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function share(Request $request): array
    {
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        if ($user) {
            $language = $user->language;
        } else {
            $language = Language::where('code', App::currentLocale())->first();
        }
        if (!$language) {
            $language = Language::where('code', 'en')->first();
        }

        $firstLoadOnlyProps = (!$request->inertia() or Session::get('redirectFromLogin')) ? [

            'localeData' =>
                [
                    'language'        => LanguageResource::make($language)->getArray(),
                    'languageOptions' => GetLanguagesOptions::make()->translated(),
                ],


            'layout'   => function () use ($user) {
                if ($user) {
                    return GetLayout::run($user);
                } else {
                    return [];
                }
            }
        ] : [];

        Session::forget('redirectFromLogin');


        return array_merge(
            parent::share($request),
            $firstLoadOnlyProps,
            [
                'auth' => [
                    'user' => $request->user(),
                ],
                'ziggy' => function () use ($request) {
                    return array_merge((new Ziggy())->toArray(), [
                        'location' => $request->url(),
                    ]);
                },

            ]
        );
    }
}
