<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:46:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\CRM\Prospect\ProspectStateEnum;
use App\Stubs\Migrations\HasContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasContact;
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('name', 256)->nullable()->collation('und_ns');
            $table = $this->contactFields(table: $table, withWebsite: true);
            $table->jsonb('location');
            $table->string('state')->index()->default(ProspectStateEnum::NO_CONTACTED->value);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->string('source_id')->nullable()->unique();
        });
        DB::statement('CREATE INDEX ON prospects USING gin (name gin_trgm_ops) ');
        DB::statement('CREATE INDEX ON prospects USING gin (contact_name gin_trgm_ops) ');
        DB::statement('CREATE INDEX ON prospects USING gin (company_name gin_trgm_ops) ');
    }


    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
