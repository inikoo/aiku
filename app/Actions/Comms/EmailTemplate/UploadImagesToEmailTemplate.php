<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\OrgAction;
use App\Actions\Web\WithUploadWebImage;
use App\Models\Comms\EmailTemplate;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class UploadImagesToEmailTemplate extends OrgAction
{
    use WithUploadWebImage;

    public function asController(EmailTemplate $emailtemplate, ActionRequest $request): Collection
    {
        $this->scope = $emailtemplate->shop;
        $this->initialisationFromShop($this->scope, $request);

        return $this->handle($emailtemplate->website, 'email_template', $this->validatedData);
    }
}
