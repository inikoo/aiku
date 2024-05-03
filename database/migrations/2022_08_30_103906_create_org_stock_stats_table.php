<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 10:24:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    use HasSalesIntervals;

    public function up(): void
    {

        Schema::create('org_stock_stats', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedInteger('org_stock_id')->index();
            $table->foreign('org_stock_id')->references('id')->on('org_stocks');

            $table->unsignedSmallInteger('number_locations')->default(0);

            $table=$this->salesIntervalFields($table, ['shop_amount', 'org_amount', 'group_amount']);

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_stock_stats');
    }
};
