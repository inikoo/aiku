<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 16 Aug 2023 08:09:28 Malaysia Time, Pantai Lembeng, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Upload;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\Upload;

class UpdateUpload extends OrgAction
{
    use WithActionUpdate;


    public function handle(Upload $upload, array $modelData): Upload
    {
        return $this->update($upload, $modelData);
    }

    public function rules(): array
    {
        $rules = [

        ];

        if (!$this->strict) {
            $rules['number_rows']     = ['sometimes', 'numeric'];
            $rules['number_success']  = ['sometimes', 'numeric'];
            $rules['number_fails']    = ['sometimes', 'numeric'];
            $rules['uploaded_at']     = ['sometimes', 'date'];
            $rules['last_fetched_at'] = ['sometimes', 'date'];
        }

        return $rules;
    }

    public function action(Upload $upload, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit = true): Upload
    {
        $this->strict = $strict;
        if (!$audit) {
            Upload::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationForGroup($upload->group, $modelData);

        return $this->handle($upload, $this->validatedData);
    }


}
