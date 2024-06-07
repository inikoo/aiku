<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote;

use App\Models\Dispatching\DeliveryNote;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDeliveryNote
{
    use AsAction;
    public function handle(DeliveryNote $deliveryNote): ?bool
    {
        $deliveryNote->deliveryNoteItems()->delete();
        return $deliveryNote->delete();
    }
}
