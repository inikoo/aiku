<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 12:25:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('stored_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('reference')->index()->collation('und_ci');
            $table->string('status')->default(StoredItemStatusEnum::IN_PROCESS->value);
            $table->string('state')->index()->default(StoredItemStateEnum::IN_PROCESS->value);
            $table->string('type')->index();
            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->unsignedInteger('location_id')->index()->nullable();
            $table->foreign('location_id')->references('id')->on('locations');
            $table->string('notes');
            $table->boolean('return_requested')->default(false);
            $table->timestampsTz();
            $table->dateTimeTz('received_at')->nullable();
            $table->dateTimeTz('booked_in_at')->nullable();
            $table->dateTimeTz('settled_at')->nullable();
            $table->jsonb('data');
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('stored_items');
    }
};
