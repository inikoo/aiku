<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 16:09:05 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\InOrganisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Procurement\OrgSupplierProduct
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $supplier_product_id
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $source_id
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @method static Builder|OrgSupplierProduct newModelQuery()
 * @method static Builder|OrgSupplierProduct newQuery()
 * @method static Builder|OrgSupplierProduct query()
 * @mixin Eloquent
 */
class OrgSupplierProduct extends Model
{
    use InOrganisation;

    protected $table = 'org_supplier_products';

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


}
