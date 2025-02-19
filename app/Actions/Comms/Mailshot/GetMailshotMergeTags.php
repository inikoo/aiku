<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Mailshot;

use App\Enums\Comms\Outbox\OutboxMergeTagsEnum;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class GetMailshotMergeTags
{
    use AsAction;
    use WithAttributes;


    public function handle(): array
    {
        return OutboxMergeTagsEnum::tags();
    }

    public function asController(): array
    {
        return $this->handle();
    }
}
