<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 31 Aug 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Actions\Traits\Hydrators\WithLeadTimeMeasurement;
use App\Models\Inventory\OrgStock;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class OrgStockHydrateLeadTime implements ShouldBeUnique
{
    use WithHydrateCommand;
    use WithLeadTimeMeasurement;

    public string $commandSignature = 'hydrate:org-stock-lead-time {organisations?*} {--s|slugs=}';

    public function __construct()
    {
        $this->model = OrgStock::class;
    }

    public function getJobUniqueId(OrgStock $orgStock): string
    {
        return $orgStock->id;
    }

    public function handle(OrgStock $orgStock): void
    {
        $orgStock->update($this->measureLeadTime('org_stock_id', $orgStock->id));
    }
}
