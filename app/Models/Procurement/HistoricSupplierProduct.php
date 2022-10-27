<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Procurement\HistoricSupplierProduct
 *
 * @property int $id
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string|null $deleted_at
 * @property int|null $supplier_product_id
 * @property string $cost unit cost
 * @property string|null $code
 * @property string|null $name
 * @property int|null $pack units per pack
 * @property int|null $outer units per outer
 * @property int|null $carton units per carton
 * @property string|null $cbm to be deleted
 * @property int|null $currency_id
 * @property int|null $source_id
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereCarton($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereCbm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereOuter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct wherePack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricSupplierProduct whereSupplierProductId($value)
 * @mixin \Eloquent
 */
class HistoricSupplierProduct extends Model
{

}
