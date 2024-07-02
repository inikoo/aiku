<?php

namespace App\Actions\Mail\Outbox\UI;

use App\Models\Mail\Outbox;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOutboxShowcase
{
    use AsObject;

    public function handle(Outbox $outbox): array
    {
        return [
            []
        ];
    }
}
