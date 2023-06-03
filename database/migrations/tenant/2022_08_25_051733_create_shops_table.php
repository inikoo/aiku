<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 13:18:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Enums\Marketing\Shop\ShopStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->unique()->collation('und_ns_ci');
            $table->string('name')->collation('und_ns_ci_ai');
            $table->string('company_name', 256)->nullable()->collation('und_ns_ci_ai');
            $table->string('contact_name', 256)->nullable()->collation('und_ns_ci_ai');
            $table->string('email')->nullable()->collation('und_ns_ci');
            $table->string('phone')->nullable()->collation('und_ns');
            $table->string('identity_document_type')->nullable();
            $table->string('identity_document_number')->nullable()->collation('und_ns_ci');
            $table->unsignedInteger('address_id')->nullable()->index();
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->jsonb('location');
            $table->string('state')->index()->default(ShopStateEnum::IN_PROCESS->value);
            $table->string('type')->index();
            $table->string('subtype')->nullable();
            $table->date('open_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->unsignedSmallInteger('country_id');
            $table->foreign('country_id')->references('id')->on('public.countries');
            $table->unsignedSmallInteger('language_id');
            $table->foreign('language_id')->references('id')->on('public.languages');
            $table->unsignedSmallInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('public.currencies');
            $table->unsignedSmallInteger('timezone_id');
            $table->foreign('timezone_id')->references('id')->on('public.timezones');
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedSmallInteger('source_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
