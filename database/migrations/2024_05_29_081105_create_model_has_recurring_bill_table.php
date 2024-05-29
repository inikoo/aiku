<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('model_has_recurring_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recurring_bill_id');
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->timestampsTz();

            $table->foreign('recurring_bill_id')
            ->references('id')
            ->on('recurring_bills')
            ->onDelete('cascade');
      
            $table->index(['model_id', 'model_type']);
            $table->unique(['recurring_bill_id','model_type','model_type_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('model_has_recurring_bill');
    }
};
