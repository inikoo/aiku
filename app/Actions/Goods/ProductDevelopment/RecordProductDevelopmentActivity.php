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

    public const string FILES_UPLOADED = 'files_uploaded';

    public const string SUPPLIER_PRODUCT_LINKED = 'supplier_product_linked';

    public const string DESIGN_VERSION_STARTED = 'design_version_started';

    public const string DESIGN_SUBMITTED = 'design_submitted';

    public const string DESIGN_APPROVED = 'design_approved';

    public const string DESIGN_CHANGES_REQUESTED = 'design_changes_requested';

    public const string SIGNED_OFF = 'signed_off';

    public const string REMINDERS_SENT = 'reminders_sent';

    public const string DESIGN_ROUND_SKIPPED = 'design_round_skipped';

    public const string DESIGN_ROUND_UNSKIPPED = 'design_round_unskipped';

    public const string SIGN_OFF_WITHDRAWN = 'sign_off_withdrawn';

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
