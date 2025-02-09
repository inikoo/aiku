<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Feb 2025 19:51:49 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Cornea\UI;

use App\Actions\Cornea\UI\Layout\GetCorneaLayout;
use App\Actions\Helpers\Language\UI\GetLanguagesOptions;
use App\Http\Resources\Helpers\LanguageResource;
use App\Models\Helpers\Language;
use App\Models\SupplyChain\SupplierUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Lorisleiva\Actions\Concerns\AsObject;

class GetCorneaFirstLoadProps
{
    use AsObject;

    public function handle(Request $request, ?SupplierUser $SupplierUser): array
    {
        if ($SupplierUser) {
            $language = $SupplierUser->language;
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


            'layout'      => GetCorneaLayout::run($request, $SupplierUser),
            'environment' => app()->environment(),
        ];
    }
}
