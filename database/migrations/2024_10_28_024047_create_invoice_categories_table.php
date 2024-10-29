<?php

use App\Enums\Accounting\Invoice\InvoiceCategoryStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('name');
            $table->string('state')->default(InvoiceCategoryStateEnum::IN_PROCESS);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('invoice_categories');
    }
};