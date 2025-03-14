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
                // Get all columns containing "baskets_created" or "baskets_updated"
                $columnsToDrop = collect(\DB::select("
                    SELECT column_name FROM information_schema.columns 
                    WHERE table_name = '{$tableName}' 
                    AND (column_name LIKE '%baskets_created%' OR column_name LIKE '%baskets_updated%')
                "))->pluck('column_name')->toArray();
    
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
