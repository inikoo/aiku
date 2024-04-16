<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 01 Sept 2022 18:53:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('historic_outerables', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);


            $table->string('slug')->unique()->collation('und_ns');
            $table->boolean('status')->index();

            $table->unsignedInteger('product_id')->index();
            $table->foreign('product_id')->references('id')->on('products');

            $table->string('outerable_type')->index();
            $table->unsignedInteger('outerable_id')->index();

            $table->unsignedDecimal('price', 18)->comment('unit price');
            $table->string('code')->nullable();
            $table->string('name', 255)->nullable();
            $table->unsignedDecimal('units', 12, 3)->nullable()->comment('units in outer');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->string('source_id')->nullable()->unique();
            $table->unique(['outerable_type', 'outerable_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('historic_outerables');
    }
};
