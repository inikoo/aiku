<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 17:02:34 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Market\Rental\RentalStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->boolean('status')->default(false)->index();
            $table->string('state')->default(RentalStateEnum::IN_PROCESS)->index();
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletes();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
