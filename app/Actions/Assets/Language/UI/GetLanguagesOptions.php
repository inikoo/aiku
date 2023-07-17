<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 12:39:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Assets\Language\UI;

use App\Models\Assets\Language;
use Lorisleiva\Actions\Concerns\AsObject;

class GetLanguagesOptions
{
    use AsObject;

    public function handle($languages): array
    {
        $selectOptions = [];
        /** @var Language $language */
        foreach ($languages as $language) {
            $selectOptions[] =
                [
                    'id'    => $language->id,
                    'name'  => $language->name,
                    'code'  => $language->code,
                ];
        }

        return $selectOptions;
    }

    public function all(): array
    {
        return $this->handle(Language::all());
    }

    public function translated(): array
    {
        return $this->handle(Language::where('status', true)->get());
    }

}
