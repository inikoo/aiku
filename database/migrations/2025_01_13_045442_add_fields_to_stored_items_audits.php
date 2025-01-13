<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Jan 2025 13:00:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('stored_item_audits', function (Blueprint $table) {
            $table->dateTimeTz('date')->index();
            $table->decimal('net_amount', 16)->default(0);
            $table->decimal('grp_net_amount', 16)->nullable();
            $table->decimal('org_net_amount', 16)->nullable();
            $table->unsignedSmallInteger('tax_category_id')->nullable()->index();
            $table->foreign('tax_category_id')->references('id')->on('tax_categories');
            $table->decimal('tax_amount', 16)->nullable();
            $table->decimal('total_amount', 16)->nullable();


            $table->unsignedSmallInteger('number_pallets')->default(0);
            $table->unsignedSmallInteger('number_stored_items')->default(0);
            $table->unsignedSmallInteger('number_added_stored_items')->default(0);
            $table->unsignedSmallInteger('number_edited_stored_items')->default(0);
            $table->unsignedSmallInteger('number_removed_stored_items')->default(0);
        });
    }


    public function down(): void
    {
        Schema::table('stored_item_audits', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('net_amount');
            $table->dropColumn('grp_net_amount');
            $table->dropColumn('org_net_amount');
            $table->dropColumn('tax_category_id');
            $table->dropColumn('tax_amount');
            $table->dropColumn('number_pallets');
            $table->dropColumn('number_stored_items');
            $table->dropColumn('number_added_stored_items');
            $table->dropColumn('number_edited_stored_items');
            $table->dropColumn('number_removed_stored_items');


        });
    }
};
