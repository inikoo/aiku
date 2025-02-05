<?php

use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasDateIntervalsStats;

    public function up(): void
    {
        Schema::create('country_ordering_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->unsignedSmallInteger('country_id');
            $table = $this->unsignedIntegerDateIntervals($table, [
                'invoices',
                'refunds',
                'orders',
                'delivery_notes',
                'registrations',
                'customers_invoiced'
            ]);
            $table->timestampsTz();
            $table->index(['model_type', 'model_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('country_ordering_intervals');
    }
};
