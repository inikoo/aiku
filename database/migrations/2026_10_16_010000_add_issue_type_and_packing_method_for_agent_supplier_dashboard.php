<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('procurement_notes', function (Blueprint $table) {
            $table->string('incident_type')->nullable()->index();
            $table->decimal('incident_quantity', 16, 3)->nullable();
        });

        foreach (['suppliers', 'supplier_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('packing_method')->nullable();
                $table->string('packing_material')->nullable();
                $table->unsignedInteger('packaging_supplier_id')->nullable()->index();
                $table->foreign('packaging_supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['suppliers', 'supplier_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['packaging_supplier_id']);
                $table->dropColumn(['packing_method', 'packing_material', 'packaging_supplier_id']);
            });
        }

        Schema::table('procurement_notes', function (Blueprint $table) {
            $table->dropColumn(['incident_type', 'incident_quantity']);
        });
    }
};
