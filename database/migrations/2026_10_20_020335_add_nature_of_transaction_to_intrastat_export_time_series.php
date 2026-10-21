<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('intrastat_export_time_series', function (Blueprint $table) {
            $table->dropUnique('intrastat_export_ts_unique');
            $table->string('nature_of_transaction', 10)->nullable()->after('partner_tax_number')->comment('Intrastat nature of transaction, a declaration line of its own');
        });

        DB::statement('
            UPDATE intrastat_export_time_series AS series
            SET nature_of_transaction = records.nature_of_transaction
            FROM (
                SELECT intrastat_export_time_series_id, MIN(nature_of_transaction) AS nature_of_transaction
                FROM intrastat_export_time_series_records
                GROUP BY intrastat_export_time_series_id
                HAVING COUNT(DISTINCT nature_of_transaction) = 1
            ) AS records
            WHERE series.id = records.intrastat_export_time_series_id
        ');

        DB::statement('
            DELETE FROM intrastat_export_time_series AS duplicate
            USING intrastat_export_time_series AS kept
            WHERE duplicate.id > kept.id
              AND duplicate.organisation_id = kept.organisation_id
              AND duplicate.tariff_code = kept.tariff_code
              AND duplicate.country_id = kept.country_id
              AND duplicate.origin_country_id IS NOT DISTINCT FROM kept.origin_country_id
              AND duplicate.tax_category_id IS NOT DISTINCT FROM kept.tax_category_id
              AND duplicate.partner_tax_number IS NOT DISTINCT FROM kept.partner_tax_number
              AND duplicate.nature_of_transaction IS NOT DISTINCT FROM kept.nature_of_transaction
              AND duplicate.frequency = kept.frequency
        ');

        DB::statement('
            CREATE UNIQUE INDEX intrastat_export_ts_unique ON intrastat_export_time_series
            (organisation_id, tariff_code, country_id, origin_country_id, tax_category_id, partner_tax_number, nature_of_transaction, frequency)
            NULLS NOT DISTINCT
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX intrastat_export_ts_unique');

        Schema::table('intrastat_export_time_series', function (Blueprint $table) {
            $table->dropColumn('nature_of_transaction');
            $table->unique(
                ['organisation_id', 'tariff_code', 'country_id', 'origin_country_id', 'tax_category_id', 'partner_tax_number', 'frequency', 'from', 'to'],
                'intrastat_export_ts_unique'
            );
        });
    }
};
