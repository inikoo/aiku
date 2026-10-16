<?php

/*
 * Author Louis Perez
 * Created on 16-10-2026-01h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment;

use App\Models\Goods\ProductDevelopment;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\AuditCustom;

class RecordProductDevelopmentActivity
{
    public const string STAGE_MOVED = 'stage_moved';

    public const string SKUS_ADDED = 'skus_added';

    public const string MEMBERS_ADDED = 'members_added';

    public const string MEMBERS_REMOVED = 'members_removed';

    /**
     * @param array<string, mixed> $old
     * @param array<string, mixed> $new
     */
    public static function record(ProductDevelopment $productDevelopment, string $event, array $old, array $new): void
    {
        $productDevelopment->auditEvent     = $event;
        $productDevelopment->isCustomEvent  = true;
        $productDevelopment->auditCustomOld = $old;
        $productDevelopment->auditCustomNew = $new;
        Event::dispatch(new AuditCustom($productDevelopment));
        $productDevelopment->isCustomEvent  = false;
        $productDevelopment->auditCustomOld = $productDevelopment->auditCustomNew = [];
    }
}
