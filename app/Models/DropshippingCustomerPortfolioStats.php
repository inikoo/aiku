<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $dropshipping_customer_portfolio_id
 * @property int $amount
 * @property int $number_orders
 * @property int $number_ordered_quantity
 * @property int $number_clients
 * @property int|null $last_ordered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DropshippingCustomerPortfolioStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DropshippingCustomerPortfolioStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DropshippingCustomerPortfolioStats query()
 * @mixin \Eloquent
 */
class DropshippingCustomerPortfolioStats extends Model
{
}
