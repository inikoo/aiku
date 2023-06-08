<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 12:29:00 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Enums\Sales\Customer\CustomerStateEnum;
use App\Enums\Sales\Customer\CustomerTradeStateEnum;
use App\Stubs\Migrations\HasContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasContact;

    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id')->index()->nullable();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('reference')->nullable()->collation('und_ns_ci')->comment('customer public id');
            $table->string('name', 256)->nullable()->collation('und_ns_ci_ai');
            $table = $this->contactFields(table: $table, withWebsite: true);
            $table->jsonb('location');
            $table->string('status')->index();
            $table->string('state')->index()->default(CustomerStateEnum::IN_PROCESS->value);
            $table->string('trade_state')->index()->default(CustomerTradeStateEnum::NONE->value)->comment('number of invoices');
            $table->boolean('is_fulfilment')->index()->default(false);
            $table->boolean('is_dropshipping')->index()->default(false);
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
            $table->unique(['shop_id', 'reference']);
        });
        DB::statement('CREATE INDEX ON customers USING gin (name gin_trgm_ops) ');
        DB::statement('CREATE INDEX ON customers USING gin (reference gin_trgm_ops) ');
        DB::statement('CREATE INDEX ON customers USING gin (contact_name gin_trgm_ops) ');
        DB::statement('CREATE INDEX ON customers USING gin (company_name gin_trgm_ops) ');


    }


    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
