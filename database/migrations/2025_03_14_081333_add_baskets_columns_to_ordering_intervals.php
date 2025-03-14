<?php

use App\Stubs\Migrations\HasDateIntervalsStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HasDateIntervalsStats;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisation_ordering_intervals', function (Blueprint $table) {
            $table = $this->unsignedIntegerDateIntervals($table, [
                'baskets_created',
                'baskets_updated',
            ]);
        });
        Schema::table('shop_ordering_intervals', function (Blueprint $table) {
            $table = $this->unsignedIntegerDateIntervals($table, [
                'baskets_created',
                'baskets_updated',
            ]);
        });
        Schema::table('group_ordering_intervals', function (Blueprint $table) {
            $table = $this->unsignedIntegerDateIntervals($table, [
                'baskets_created',
                'baskets_updated',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'organisation_ordering_intervals',
            'shop_ordering_intervals',
            'group_ordering_intervals',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columnsToDrop = collect(\DB::select("SHOW COLUMNS FROM {$tableName}"))
                    ->pluck('Field')
                    ->filter(fn($column) => str_contains($column, 'baskets_created') || str_contains($column, 'baskets_updated'))
                    ->toArray();

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
