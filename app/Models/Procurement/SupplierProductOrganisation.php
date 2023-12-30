<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\SupplierProductOrganisation
 *
 * @property int $id
 * @property int $supplier_product_id
 * @property int $organisation_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $source_id
 * @method static Builder|SupplierProductOrganisation newModelQuery()
 * @method static Builder|SupplierProductOrganisation newQuery()
 * @method static Builder|SupplierProductOrganisation query()
 * @mixin Eloquent
 */
class SupplierProductOrganisation extends Pivot
{
    protected $table = 'supplier_product_organisation';
}
