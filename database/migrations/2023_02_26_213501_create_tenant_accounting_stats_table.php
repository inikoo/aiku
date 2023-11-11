<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:47:47 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasPaymentStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasPaymentStats;

    public function up(): void
    {
        Schema::create('tenant_accounting_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('number_payment_service_providers')->default(0);
            $table->unsignedInteger('number_payment_accounts')->default(0);

            $table = $this->paymentStats($table);


            $table->unsignedInteger('number_invoices')->default(0);
            $table->unsignedInteger('number_invoices_type_invoice')->default(0);
            $table->unsignedInteger('number_invoices_type_refund')->default(0);


            $table->timestampsTz();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('tenant_accounting_stats');
    }
};
