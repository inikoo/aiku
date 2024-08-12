<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Models\Catalogue\Asset;
use App\Models\Helpers\Currency;
use App\Models\Ordering\Transaction;
use App\Models\Traits\InCustomer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property \Illuminate\Support\Carbon $date
 * @property int $shop_id
 * @property int|null $invoice_id
 * @property int $customer_id
 * @property int $asset_id
 * @property int $historic_asset_id
 * @property int|null $family_id
 * @property int|null $department_id
 * @property int|null $order_id
 * @property int|null $transaction_id
 * @property int|null $recurring_bill_transaction_id
 * @property string $quantity
 * @property string $gross_amount
 * @property string $net_amount
 * @property int $tax_category_id
 * @property string|null $grp_exchange
 * @property string|null $org_exchange
 * @property string|null $grp_net_amount
 * @property string|null $org_net_amount
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property int|null $source_alt_id
 * @property-read Asset $asset
 * @property-read Currency|null $currency
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Model|\Eloquent $item
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
 * @property-read Transaction|null $transaction
 * @method static Builder|InvoiceTransaction newModelQuery()
 * @method static Builder|InvoiceTransaction newQuery()
 * @method static Builder|InvoiceTransaction onlyTrashed()
 * @method static Builder|InvoiceTransaction query()
 * @method static Builder|InvoiceTransaction withTrashed()
 * @method static Builder|InvoiceTransaction withoutTrashed()
 * @mixin Eloquent
 */
class InvoiceTransaction extends Model
{
    use SoftDeletes;
    use InCustomer;

    protected $table = 'invoice_transactions';

    protected $casts = [
        'data'           => 'array',
        'date'           => 'datetime',
        'quantity'       => 'decimal:3',
        'gross_amount'   => 'decimal:2',
        'net_amount'     => 'decimal:2',
        'grp_exchange'   => 'decimal:4',
        'org_exchange'   => 'decimal:4',
        'grp_net_amount' => 'decimal:2',
        'org_net_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

}
