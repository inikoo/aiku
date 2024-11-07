<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\OrgAction;
use App\Actions\Web\WithUploadWebImage;
use App\Models\Mail\EmailTemplate;
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
