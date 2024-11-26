<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:00:13 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Catalogue\DepartmentStats
 *
 * @property int $id
 * @property int $product_category_id
 * @property int $number_sub_departments
 * @property int $number_current_sub_departments state: active+discontinuing
 * @property int $number_sub_departments_state_in_process
 * @property int $number_sub_departments_state_active
 * @property int $number_sub_departments_state_discontinuing
 * @property int $number_sub_departments_state_discontinued
 * @property int $number_families
 * @property int $number_current_families state: active+discontinuing
 * @property int $number_families_state_in_process
 * @property int $number_families_state_active
 * @property int $number_families_state_discontinuing
 * @property int $number_families_state_discontinued
 * @property int $number_orphan_families
 * @property int $number_products
 * @property int $number_current_products state: active+discontinuing
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property int $number_rentals
 * @property int $number_rentals_state_in_process
 * @property int $number_rentals_state_active
 * @property int $number_rentals_state_discontinued
 * @property int $number_services
 * @property int $number_services_state_in_process
 * @property int $number_services_state_active
 * @property int $number_services_state_discontinued
 * @property int $number_subscriptions
 * @property int $number_subscriptions_state_in_process
 * @property int $number_subscriptions_state_active
 * @property int $number_subscriptions_state_discontinued
 * @property int|null $top_1d_family_id
 * @property int|null $top_1d_product_id
 * @property int|null $top_1w_family_id
 * @property int|null $top_1w_product_id
 * @property int|null $top_1m_family_id
 * @property int|null $top_1m_product_id
 * @property int|null $top_1y_family_id
 * @property int|null $top_1y_product_id
 * @property int|null $top_all_family_id
 * @property int|null $top_all_product_id
 * @property int $number_customers_who_favourited
 * @property int $number_customers_who_un_favourited
 * @property int $number_customers_who_reminded
 * @property int $number_customers_who_un_reminded
 * @property string $shop_amount_all
 * @property string $shop_amount_1y
 * @property string $shop_amount_1q
 * @property string $shop_amount_1m
 * @property string $shop_amount_1w
 * @property string $shop_amount_3d
 * @property string $shop_amount_1d
 * @property string $shop_amount_ytd
 * @property string $shop_amount_qtd
 * @property string $shop_amount_mtd
 * @property string $shop_amount_wtd
 * @property string $shop_amount_lm
 * @property string $shop_amount_lw
 * @property string $shop_amount_all_ly
 * @property string $shop_amount_1y_ly
 * @property string $shop_amount_1q_ly
 * @property string $shop_amount_1m_ly
 * @property string $shop_amount_1w_ly
 * @property string $shop_amount_3d_ly
 * @property string $shop_amount_1d_ly
 * @property string $shop_amount_ytd_ly
 * @property string $shop_amount_qtd_ly
 * @property string $shop_amount_mtd_ly
 * @property string $shop_amount_wtd_ly
 * @property string $shop_amount_lm_ly
 * @property string $shop_amount_lw_ly
 * @property string $shop_amount_py1
 * @property string $shop_amount_py2
 * @property string $shop_amount_py3
 * @property string $shop_amount_py4
 * @property string $shop_amount_py5
 * @property string $shop_amount_pq1
 * @property string $shop_amount_pq2
 * @property string $shop_amount_pq3
 * @property string $shop_amount_pq4
 * @property string $shop_amount_pq5
 * @property string $sales_org_currency_all
 * @property string $sales_org_currency_1y
 * @property string $sales_org_currency_1q
 * @property string $sales_org_currency_1m
 * @property string $sales_org_currency_1w
 * @property string $sales_org_currency_3d
 * @property string $sales_org_currency_1d
 * @property string $sales_org_currency_ytd
 * @property string $sales_org_currency_qtd
 * @property string $sales_org_currency_mtd
 * @property string $sales_org_currency_wtd
 * @property string $sales_org_currency_lm
 * @property string $sales_org_currency_lw
 * @property string $sales_org_currency_all_ly
 * @property string $sales_org_currency_1y_ly
 * @property string $sales_org_currency_1q_ly
 * @property string $sales_org_currency_1m_ly
 * @property string $sales_org_currency_1w_ly
 * @property string $sales_org_currency_3d_ly
 * @property string $sales_org_currency_1d_ly
 * @property string $sales_org_currency_ytd_ly
 * @property string $sales_org_currency_qtd_ly
 * @property string $sales_org_currency_mtd_ly
 * @property string $sales_org_currency_wtd_ly
 * @property string $sales_org_currency_lm_ly
 * @property string $sales_org_currency_lw_ly
 * @property string $sales_org_currency_py1
 * @property string $sales_org_currency_py2
 * @property string $sales_org_currency_py3
 * @property string $sales_org_currency_py4
 * @property string $sales_org_currency_py5
 * @property string $sales_org_currency_pq1
 * @property string $sales_org_currency_pq2
 * @property string $sales_org_currency_pq3
 * @property string $sales_org_currency_pq4
 * @property string $sales_org_currency_pq5
 * @property string $sales_grp_currencyall
 * @property string $sales_grp_currency1y
 * @property string $sales_grp_currency1q
 * @property string $sales_grp_currency1m
 * @property string $sales_grp_currency1w
 * @property string $sales_grp_currency3d
 * @property string $sales_grp_currency1d
 * @property string $sales_grp_currencyytd
 * @property string $sales_grp_currencyqtd
 * @property string $sales_grp_currencymtd
 * @property string $sales_grp_currencywtd
 * @property string $sales_grp_currencylm
 * @property string $sales_grp_currencylw
 * @property string $sales_grp_currencyall_ly
 * @property string $sales_grp_currency1y_ly
 * @property string $sales_grp_currency1q_ly
 * @property string $sales_grp_currency1m_ly
 * @property string $sales_grp_currency1w_ly
 * @property string $sales_grp_currency3d_ly
 * @property string $sales_grp_currency1d_ly
 * @property string $sales_grp_currencyytd_ly
 * @property string $sales_grp_currencyqtd_ly
 * @property string $sales_grp_currencymtd_ly
 * @property string $sales_grp_currencywtd_ly
 * @property string $sales_grp_currencylm_ly
 * @property string $sales_grp_currencylw_ly
 * @property string $sales_grp_currencypy1
 * @property string $sales_grp_currencypy2
 * @property string $sales_grp_currencypy3
 * @property string $sales_grp_currencypy4
 * @property string $sales_grp_currencypy5
 * @property string $sales_grp_currencypq1
 * @property string $sales_grp_currencypq2
 * @property string $sales_grp_currencypq3
 * @property string $sales_grp_currencypq4
 * @property string $sales_grp_currencypq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder<static>|ProductCategoryStats newModelQuery()
 * @method static Builder<static>|ProductCategoryStats newQuery()
 * @method static Builder<static>|ProductCategoryStats query()
 * @mixin Eloquent
 */
class ProductCategoryStats extends Model
{
    protected $table = 'product_category_stats';

    protected $guarded = [];


}
