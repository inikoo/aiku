<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:36:20 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Web\Website\WebsiteCloudflareStatusEnum;
use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->string('type');
            $table->string('state')->default(WebsiteStateEnum::IN_PROCESS->value)->index();
            $table->string('engine')->default(WebsiteEngineEnum::IRIS->value)->index();
            $table->string('code')->unique()->collation('und_ns');
            $table->string('domain')->collation('und_ns');
            $table->string('name')->collation('und_ns');
            $table->jsonb('settings');
            $table->jsonb('data');
            $table->jsonb('structure');
            $table->boolean('in_maintenance')->default(false);
            $table->unsignedSmallInteger('current_layout_id')->index()->nullable();
            $table->timestampsTz();
            $table->timestampTz('launched_at')->nullable();
            $table->timestampTz('closed_at')->nullable();
            $table->softDeletesTz();
            $table->string('cloudflare_id')->index()->nullable();
            $table->string('cloudflare_status')->nullable()->default(WebsiteCloudflareStatusEnum::NOT_SET->value);
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
