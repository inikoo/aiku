<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 22 Oct 2024 00:46:50 Central Indonesia Time, Office, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Rules;

trait WithNoStrictRules
{
    protected function noStrictRules($rules): array
    {
        $rules['created_at'] = ['sometimes', 'date'];
        $rules['fetched_at'] = ['sometimes', 'date'];
        $rules['deleted_at'] = ['sometimes', 'nullable', 'date'];
        $rules['source_id']  = ['sometimes', 'string', 'max:255'];
        return $rules;

    }
}
