<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 17 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\ActionRequest;

trait WithPalletDocumentAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        $record = $request->route('palletReturn') ?? $request->route('palletDelivery');

        return ($record instanceof PalletDelivery || $record instanceof PalletReturn)
            && $record->documentsCanBeReadBy($request->user());
    }
}
