<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-16h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;
use App\Models\Goods\TradeUnit;
use App\Models\SupplyChain\SupplierProduct;

enum ProductDevelopmentTargetFieldEnum: string
{
    use EnumHelperTrait;

    case TRADE_UNIT_NET_WEIGHT            = 'trade_unit.net_weight';
    case TRADE_UNIT_GROSS_WEIGHT          = 'trade_unit.gross_weight';
    case TRADE_UNIT_MARKETING_DIMENSIONS  = 'trade_unit.marketing_dimensions';
    case TRADE_UNIT_ORIGIN_COUNTRY        = 'trade_unit.origin_country_id';
    case TRADE_UNIT_BARCODE               = 'trade_unit.barcode';
    case TRADE_UNIT_TARIFF_CODE           = 'trade_unit.tariff_code';
    case TRADE_UNIT_CPNP_NUMBER           = 'trade_unit.cpnp_number';
    case TRADE_UNIT_UFI_NUMBER            = 'trade_unit.ufi_number';
    case TRADE_UNIT_SCPN_NUMBER           = 'trade_unit.scpn_number';
    case TRADE_UNIT_UN_NUMBER             = 'trade_unit.un_number';
    case TRADE_UNIT_MARKETING_INGREDIENTS = 'trade_unit.marketing_ingredients';
    case TRADE_UNIT_GPSR_MANUFACTURER     = 'trade_unit.gpsr_manufacturer';
    case TRADE_UNIT_GPSR_EU_RESPONSIBLE   = 'trade_unit.gpsr_eu_responsible';
    case TRADE_UNIT_GPSR_WARNINGS         = 'trade_unit.gpsr_warnings';
    case TRADE_UNIT_GPSR_CLASS_LANGUAGES  = 'trade_unit.gpsr_class_languages';
    case TRADE_UNIT_COST_PRICE            = 'trade_unit.cost_price';

    case SUPPLIER_PRODUCT_UNITS_PER_PACK   = 'supplier_product.units_per_pack';
    case SUPPLIER_PRODUCT_UNITS_PER_CARTON = 'supplier_product.units_per_carton';
    case SUPPLIER_PRODUCT_CBM              = 'supplier_product.cbm';
    case SUPPLIER_PRODUCT_CARTON_WEIGHT    = 'supplier_product.carton_weight';
    case SUPPLIER_PRODUCT_LEAD_TIME        = 'supplier_product.estimated_lead_time_days';
    case SUPPLIER_PRODUCT_COST             = 'supplier_product.cost';

    public static function labels(): array
    {
        return [
            'trade_unit.net_weight'                     => __('Net weight'),
            'trade_unit.gross_weight'                   => __('Gross weight'),
            'trade_unit.marketing_dimensions'           => __('Dimensions'),
            'trade_unit.origin_country_id'              => __('Country of origin'),
            'trade_unit.barcode'                        => __('Barcode'),
            'trade_unit.tariff_code'                    => __('Tariff code'),
            'trade_unit.cpnp_number'                    => __('CPNP number'),
            'trade_unit.ufi_number'                     => __('UFI number'),
            'trade_unit.scpn_number'                    => __('SCPN number'),
            'trade_unit.un_number'                      => __('UN number'),
            'trade_unit.marketing_ingredients'          => __('Ingredients'),
            'trade_unit.gpsr_manufacturer'              => __('GPSR manufacturer'),
            'trade_unit.gpsr_eu_responsible'            => __('GPSR EU responsible'),
            'trade_unit.gpsr_warnings'                  => __('GPSR warnings'),
            'trade_unit.gpsr_class_languages'           => __('GPSR languages'),
            'trade_unit.cost_price'                     => __('Cost price'),
            'supplier_product.units_per_pack'           => __('Units per pack'),
            'supplier_product.units_per_carton'         => __('Units per carton'),
            'supplier_product.cbm'                      => __('Carton CBM'),
            'supplier_product.carton_weight'            => __('Carton weight'),
            'supplier_product.estimated_lead_time_days' => __('Lead time (days)'),
            'supplier_product.cost'                     => __('Supplier cost'),
        ];
    }

    public function column(): string
    {
        return explode('.', $this->value, 2)[1];
    }

    public function isOnSupplierProduct(): bool
    {
        return str_starts_with($this->value, 'supplier_product.');
    }

    public function isCost(): bool
    {
        return in_array($this, [self::TRADE_UNIT_COST_PRICE, self::SUPPLIER_PRODUCT_COST], true);
    }

    public function isFilledOn(TradeUnit $tradeUnit, ?SupplierProduct $supplierProduct): bool
    {
        $model = $this->isOnSupplierProduct() ? $supplierProduct : $tradeUnit;
        if (!$model) {
            return false;
        }

        return filled($model->getAttribute($this->column()));
    }
}
