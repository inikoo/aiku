<?php

/*
 * Author Louis Perez
 * Created on 20-10-2026-15h-10m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Json;

use App\Actions\Goods\ProductDevelopment\ProductDevelopmentWaitingOn;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\ActionRequest;

/**
 * What the rail's Developments badge shows when opened: the first things waiting on the person.
 */
class GetProductDevelopmentWaitingBadge extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public const int LIMIT = 8;

    /**
     * Opening the badge also stops it pulsing after a reminder.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, late: int, reminded: bool}
     */
    public function handle(User $user): array
    {
        ProductDevelopmentWaitingOn::markSeen($user);
        $rows = (new ProductDevelopmentWaitingOn())->forUser($user);

        return [
            'rows'  => array_slice($rows, 0, self::LIMIT),
            'total' => count($rows),
            'late'     => count(array_filter($rows, fn (array $row) => $row['is_late'])),
            'reminded' => false,
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, late: int, reminded: bool}
     */
    public function asController(ActionRequest $request): array
    {
        $this->initialisationFromGroup($request->user()->group, $request);

        return $this->handle($request->user());
    }
}
