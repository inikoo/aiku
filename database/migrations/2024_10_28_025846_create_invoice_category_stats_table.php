<?php

use App\Enums\Accounting\Invoice\InvoiceCategoryStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('invoice_category_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('invoice_category_id');
            $table->foreign('invoice_category_id')->references('id')->on('invoice_categories')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('number_invoices')->default(0);
            $table->unsignedInteger('number_customers')->default(0);
            foreach (InvoiceCategoryStateEnum::cases() as $case) {
                $table->unsignedInteger('number_invoice_category_state_'.$case->snake())->default(0);
            }
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('invoice_category_stats');
    }
};
