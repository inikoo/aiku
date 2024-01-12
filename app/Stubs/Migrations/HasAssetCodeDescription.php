<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasAssetCodeDescription
{
    public function assertCodeDescription(Blueprint $table): Blueprint
    {
        $table->string('code')->index()->collation('und_ns');
        $table->string('name', 255)->nullable();
        $table->text('description')->nullable()->fulltext();

        return $table;
    }
}
