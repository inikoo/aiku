<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Reports;

use App\Enums\Goods\Packaging\EprSchemeEnum;
use App\Models\Goods\EprSnapshot;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * The organisation's frozen packaging EPR returns, newest first.
 */
class GetEprSnapshots
{
    use AsAction;

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(Organisation $organisation): array
    {
        return EprSnapshot::where('organisation_id', $organisation->id)
            ->with('user')
            ->withCount('lines')
            ->orderByDesc('id')
            ->get()
            ->map(fn (EprSnapshot $snapshot) => [
                'id'           => $snapshot->id,
                'scheme'       => $snapshot->scheme->value,
                'scheme_label' => EprSchemeEnum::labels()[$snapshot->scheme->value],
                'from'         => $snapshot->date_from->toDateString(),
                'to'           => $snapshot->date_to->toDateString(),
                'kg'           => (float)$snapshot->kg,
                'lines'        => $snapshot->lines_count,
                'notes'        => $snapshot->notes,
                'user'         => $snapshot->user?->contact_name,
                'created_at'   => $snapshot->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
