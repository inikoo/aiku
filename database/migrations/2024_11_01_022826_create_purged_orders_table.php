<?php

use App\Enums\Ordering\Purge\PurgedOrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('purged_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('purge_id')->index();
            $table->foreign('purge_id')->references('id')->on('purges');
            $table->unsignedInteger('order_id');
            $table->string('status')->default(PurgedOrderStatusEnum::IN_PROCESS);
            $table->dateTimeTz('purged_at')->nullable();
            $table->dateTimeTz('order_last_updated_at')->nullable();
            $table->text('note')->nullable();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purge_records');
    }
};
