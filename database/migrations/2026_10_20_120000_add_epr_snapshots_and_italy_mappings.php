<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('epr_snapshots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->string('scheme', 8);
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('kg', 14, 3);
            $table->jsonb('data');
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampsTz();
        });

        Schema::create('epr_snapshot_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('epr_snapshot_id')->index();
            $table->foreign('epr_snapshot_id')->references('id')->on('epr_snapshots')->cascadeOnDelete();
            $table->string('activity', 24);
            $table->unsignedSmallInteger('counterparty_country_id')->nullable();
            $table->string('channel', 16)->nullable();
            $table->unsignedInteger('org_stock_id')->nullable();
            $table->string('org_stock_code')->nullable();
            $table->unsignedInteger('trade_unit_id')->nullable();
            $table->unsignedInteger('packaging_family_id')->nullable();
            $table->string('packaging_family_code')->nullable();
            $table->string('brand_ownership', 16)->nullable();
            $table->string('end_use', 16)->nullable();
            $table->string('family_source', 32)->nullable();
            $table->boolean('is_product_itself')->nullable();
            $table->unsignedInteger('packaging_component_id')->nullable();
            $table->string('packaging_level', 32)->nullable();
            $table->string('material_category', 32)->nullable();
            $table->string('polymer', 16)->nullable();
            $table->decimal('weight_g', 12, 3)->nullable();
            $table->boolean('weight_is_measured')->nullable();
            $table->decimal('quantity_per_unit', 12, 4)->nullable();
            $table->string('ram_rating', 8)->nullable();
            $table->date('ram_assessed_at')->nullable();
            $table->text('ram_evidence_ref')->nullable();
            $table->decimal('units', 16, 4);
            $table->decimal('kg', 14, 4);
        });

        $now   = now();
        $italy = [
            'paper_cardboard' => ['Carta'],
            'composite'       => ['Carta', 'Poliaccoppiati a prevalenza carta'],
            'plastic'         => ['Plastica'],
            'glass'           => ['Vetro'],
            'aluminium'       => ['Alluminio'],
            'steel'           => ['Acciaio'],
            'wood'            => ['Legno'],
            'textile'         => ['Altro'],
            'other'           => ['Altro'],
        ];
        $rows = [];
        foreach ($italy as $category => $names) {
            $rows[] = ['scheme' => 'it', 'material_category' => $category, 'polymer' => null, 'scheme_material' => $names[0], 'scheme_subcategory' => $names[1] ?? null, 'created_at' => $now, 'updated_at' => $now];
        }
        $rows[] = ['scheme' => 'it', 'material_category' => 'plastic', 'polymer' => 'bio', 'scheme_material' => 'Plastica biodegradabile e compostabile', 'scheme_subcategory' => null, 'created_at' => $now, 'updated_at' => $now];
        DB::table('epr_material_mappings')->insert($rows);
    }

    public function down(): void
    {
        DB::table('epr_material_mappings')->where('scheme', 'it')->delete();
        Schema::dropIfExists('epr_snapshot_lines');
        Schema::dropIfExists('epr_snapshots');
    }
};
