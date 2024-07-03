<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 13 Jul 2023 14:24:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Enums\Web\Banner\BannerStateEnum;
use App\Enums\Web\Banner\BannerTypeEnum;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSoftDeletes;
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('web_block_id');
            $table->foreign('web_block_id')->references('id')->on('web_blocks')->onUpdate('cascade')->onDelete('cascade');



            $table->ulid()->index();
            $table->string('type')->default(BannerTypeEnum::LANDSCAPE->value);

            $table->string('slug')->unique()->collation('und_ns');
            $table->string('name')->collation('und_ns_ci');
            $table->string('state')->default(BannerStateEnum::UNPUBLISHED->value);
            $table->unsignedSmallInteger('unpublished_snapshot_id')->nullable()->index();
            $table->unsignedSmallInteger('live_snapshot_id')->nullable()->index();
            $table->dateTimeTz('date')->index();
            $table->dateTimeTz('live_at')->nullable();
            $table->dateTimeTz('switch_off_at')->nullable();
            $table->jsonb('compiled_layout');
            $table->jsonb('data');
            $table->unsignedInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media');
            $table->timestampsTz();
            $this->softDeletes($table);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
