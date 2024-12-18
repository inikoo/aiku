<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Comms\Outbox;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class ToggleOutbox
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(Outbox $outbox, string $status = null): void
    {
        $this->update($outbox, [
            'state' => $status
        ]);
    }

    public function asController(Shop $shop, Outbox $outbox): void
    {
        $status = $outbox->state === OutboxStateEnum::ACTIVE ? OutboxStateEnum::SUSPENDED : OutboxStateEnum::ACTIVE;

        $this->handle($outbox, $status->value);
    }
}
