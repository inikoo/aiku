<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Jun 2023 23:56:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('workplaces', function (Blueprint $table) {
            $table->unsignedSmallInteger('id');
            $table->boolean('status')->index()->default(true);
            $table->string('type')->index()->default(WorkplaceTypeEnum::HQ->value);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('name')->collation('und_ns');
            $table->unsignedSmallInteger('timezone_id')->nullable();
            $table->foreign('timezone_id')->references('id')->on('public.timezones');
            $table->unsignedBigInteger('address_id')->nullable()->index();
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->jsonb('data');
            $table->jsonb('location');
            $table->string('owner_type');
            $table->unsignedInteger('owner_id');

            $table->timestamps();
            $table->softDeletesTz();
            $table->unique(['owner_id','owner_type']);
        });
        DB::statement('CREATE INDEX ON workplaces USING gin (name gin_trgm_ops) ');

    }


    public function down(): void
    {
        Schema::dropIfExists('workplaces');
    }
};
