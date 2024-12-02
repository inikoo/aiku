<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot;

use App\Actions\Comms\Mailshot\Hydrators\MailshotHydrateEmails;
use App\Actions\HydrateModel;
use App\Models\Comms\Mailshot;
use Illuminate\Support\Collection;

class HydrateMailshot extends HydrateModel
{
    public string $commandSignature = 'hydrate:mailshots {organisations?*} {--slugs}';

    public function handle(Mailshot $mailshot): void
    {
        MailshotHydrateEmails::run($mailshot);
    }

    protected function getModel(string $slugs): Mailshot
    {
        return Mailshot::where('slug', $slugs)->first();
    }

    protected function getAllModels(): Collection
    {
        return Mailshot::get();
    }

}
