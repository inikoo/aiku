<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:22:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Central\Domain\DomainCloudflareStatusEnum;
use App\Enums\Central\Domain\DomainIrisStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('website_id')->index();
            $table->unsignedSmallInteger('shop_id')->index();
            $table->string('domain')->index();
            $table->string('cloudflare_id')->index()->nullable();
            $table->string('cloudflare_status')->nullable()->default(DomainCloudflareStatusEnum::NOT_SET->value);
            $table->unsignedSmallInteger('iris_id')->index()->nullable();
            $table->string('iris_status')->nullable()->default(DomainIrisStatusEnum::PENDING->value);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
