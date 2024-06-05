<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 18:35:32 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasAssetCodeDescription;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAssetCodeDescription;
    use HasGroupOrganisationRelationship;
    use HasSoftDeletes;

    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('type')->index();
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedSmallInteger('department_id')->nullable();
            $table->unsignedSmallInteger('product_category_id')->nullable();

            $table->string('slug')->unique()->collation('und_ns');
            $table = $this->assertCodeDescription($table);
            $table->unsignedInteger('image_id')->nullable();

            $table->string('parent_type');
            $table->unsignedInteger('parent_id');
            $table->string('state')->nullable()->index();
            $table->jsonb('data');
            $table->timestampstz();
            $table = $this->softDeletes($table);
            $table->string('source_department_id')->nullable()->unique();
            $table->string('source_family_id')->nullable()->unique();
            $table->index(['parent_id', 'parent_type']);
        });
        DB::statement('CREATE INDEX ON product_categories USING gin (name gin_trgm_ops) ');

        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('product_categories');
            $table->foreign('product_category_id')->references('id')->on('product_categories');
        });

    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign('department_id_foreign');
            $table->dropForeign('product_category_id_foreign');
        });
        Schema::dropIfExists('product_categories');
    }
};
