<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate;

use App\Actions\InertiaAction;
use App\Models\Mail\EmailTemplate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class GetEmailTemplateCompiledLayout extends InertiaAction
{
    use AsController;

    public function handle(EmailTemplate $emailTemplate): array
    {
        return $emailTemplate->compiled;
    }

    public function authorize(ActionRequest $request): bool
    {
        // todo need to change this
        return true;
    }

    public function asController(EmailTemplate $emailTemplate): array
    {
        return $this->handle($emailTemplate);
    }


}
