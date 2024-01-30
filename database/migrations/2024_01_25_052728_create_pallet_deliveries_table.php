<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:20:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;
    use HasSoftDeletes;
    public function up(): void
    {
        Schema::create('pallet_deliveries', function (Blueprint $table) {
            $table->id();
            $table = $this->groupOrgRelationship($table);
            $table->ulid()->unique()->index();

            $table->unsignedSmallInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedSmallInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('customer_reference')->nullable()->index();
            $table->string('reference')->unique()->index();
            $table->string('state')->default(PalletDeliveryStateEnum::IN->value);
            $table->dateTimeTz('in_at')->nullable();
            $table->dateTimeTz('out_at')->nullable();
            $table->jsonb('data')->nullable();

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('pallet_deliveries');
    }
};
