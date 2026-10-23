<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    private const OPERATORS = [
        'aw' => ['code' => 'UK', 'position' => 1, 'role' => 'Imported by:', 'name' => 'Ancient Wisdom Ltd', 'short' => 'UK Ancient Wisdom Ltd', 'address' => 'Affinity Park, Europa Drive, Sheffield S9 1XT, United Kingdom', 'email' => null],
        'es' => ['code' => 'ES', 'position' => 2, 'role' => 'Importado por:', 'name' => 'AW Artisan S.L.', 'short' => 'ES AW Artisan S.L.', 'address' => 'Calle Caleta de Velez 39, 29004 Málaga, España', 'email' => 'hola@awartisan.es'],
        'sk' => ['code' => 'SK', 'position' => 3, 'role' => 'Dovozca / EU Responsible Person:', 'name' => 'Ancient Wisdom s.r.o.', 'short' => 'SK & EU RP Ancient Wisdom s.r.o.', 'address' => 'Prílohy 583/57, 919 26 Zavar, Slovensko', 'email' => 'contact@awgifts.eu'],
    ];

    public function up(): void
    {
        foreach (self::OPERATORS as $slug => $operator) {
            DB::table('organisations')->where('slug', $slug)->update([
                'settings' => DB::raw("jsonb_set(coalesce(settings::jsonb, '{}'::jsonb) || jsonb_build_object('gpsr', coalesce(settings::jsonb->'gpsr', '{}'::jsonb)), '{gpsr,operator}', ".DB::getPdo()->quote(json_encode($operator)).'::jsonb)'),
            ]);
        }

        $responsibleOrganisations = DB::table('organisations')->whereIn('slug', ['sk', 'aw'])->get(['slug', 'group_id', 'id']);

        foreach ($responsibleOrganisations->groupBy('group_id') as $groupId => $organisations) {
            $gpsr = array_filter([
                'eu_responsible_organisation_id' => $organisations->firstWhere('slug', 'sk')?->id,
                'uk_responsible_organisation_id' => $organisations->firstWhere('slug', 'aw')?->id,
            ]);

            DB::table('groups')->where('id', $groupId)->update([
                'settings' => DB::raw("coalesce(settings::jsonb, '{}'::jsonb) || jsonb_build_object('gpsr', coalesce(settings::jsonb->'gpsr', '{}'::jsonb) || ".DB::getPdo()->quote(json_encode($gpsr)).'::jsonb)'),
            ]);
        }

        DB::table('product_development_checklist_templates')->where('target_field', 'trade_unit.gpsr_eu_responsible')->update(['required' => false]);
        DB::table('product_development_checklist_items')->where('target_field', 'trade_unit.gpsr_eu_responsible')->update(['required' => false]);
    }

    public function down(): void
    {
        DB::table('organisations')->whereIn('slug', array_keys(self::OPERATORS))->update(['settings' => DB::raw("settings::jsonb #- '{gpsr,operator}'")]);
        DB::table('groups')->update(['settings' => DB::raw("settings::jsonb - 'gpsr'")]);
    }
};
