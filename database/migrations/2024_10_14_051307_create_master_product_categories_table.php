<?php

use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Stubs\Migrations\HasAssetCodeDescription;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAssetCodeDescription;
    use HasSoftDeletes;

    public function up(): void
    {
        Schema::create('master_product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->string('type')->index();
            $table->string('state')->index()->default(ProductCategoryStateEnum::IN_PROCESS->value);
            $table->unsignedInteger('master_department_id')->nullable()->index();
            $table->foreign('master_department_id')->references('id')->on('product_categories');
            $table->unsignedInteger('master_sub_department_id')->nullable()->index();
            $table->foreign('master_sub_department_id')->references('id')->on('product_categories');
            $table->unsignedInteger('master_parent_id')->nullable()->index();
            $table->foreign('master_parent_id')->references('id')->on('product_categories');
            $table->string('slug')->unique()->collation('und_ns');
            $table = $this->assertCodeDescription($table);
            $table->unsignedInteger('image_id')->nullable();
            $table->jsonb('data');
            $table->dateTimeTz('activated_at')->nullable();
            $table->dateTimeTz('discontinuing_at')->nullable();
            $table->dateTimeTz('discontinued_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_product_categories');
    }
};
