<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 12:04:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::statement("
            UPDATE supplier_products
            SET minimum_carton_order = (data->>'minimum_carton_order')::numeric::integer,
                data = data - 'minimum_carton_order'
            WHERE data->'minimum_carton_order' IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE supplier_products
            SET data = jsonb_set(coalesce(data, '{}'::jsonb), '{minimum_carton_order}', to_jsonb(minimum_carton_order))
            WHERE minimum_carton_order IS NOT NULL
        ");
    }
};
