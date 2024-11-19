<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailTemplate;

use App\Actions\InertiaAction;
use App\Models\Comms\EmailTemplate;
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
