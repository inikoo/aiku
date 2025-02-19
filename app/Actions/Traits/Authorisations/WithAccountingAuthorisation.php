<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 19 Feb 2025 11:08:12 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Traits\Authorisations;

use Lorisleiva\Actions\ActionRequest;

trait WithAccountingAuthorisation
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->authTo("accounting.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->authTo("accounting.{$this->organisation->id}.edit");

        return $request->user()->authTo(
            [
                "accounting.{$this->organisation->id}.view"
            ]
        );
    }
}
