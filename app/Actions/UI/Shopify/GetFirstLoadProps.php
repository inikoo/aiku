<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 11:59:42 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Shopify;

use App\Actions\Helpers\Language\UI\GetLanguagesOptions;
use App\Actions\UI\Retina\Layout\GetLayout;
use App\Http\Resources\Helpers\LanguageResource;
use App\Models\CRM\WebUser;
use App\Models\Helpers\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Lorisleiva\Actions\Concerns\AsObject;

class GetFirstLoadProps
{
    use AsObject;

    public function handle(Request $request, ?WebUser $webUser): array
    {
        if ($webUser) {
            $language = $webUser->language;
        } else {
            $language = Language::where('code', App::currentLocale())->first();
        }
        if (!$language) {
            $language = Language::where('code', 'en')->first();
        }


        return
            [
            'localeData' =>
                [
                    'language'        => LanguageResource::make($language)->getArray(),
                    'languageOptions' => GetLanguagesOptions::make()->translated(),
                ],

            'layout'      => GetLayout::run($request, $webUser),
            'environment' => app()->environment(),
        ];
    }
}
