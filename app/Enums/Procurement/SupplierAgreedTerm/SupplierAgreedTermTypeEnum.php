<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 01:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\SupplierAgreedTerm;

use App\Enums\EnumHelperTrait;

enum SupplierAgreedTermTypeEnum: string
{
    use EnumHelperTrait;

    case INCOTERM = 'incoterm';
    case PACKING_CHARGE = 'packing_charge';
    case LABEL_CHARGE = 'label_charge';
    case BARCODE_CHARGE = 'barcode_charge';
    case CARTON_MARKING_CHARGE = 'carton_marking_charge';
    case INSERT_OR_LEAFLET_CHARGE = 'insert_or_leaflet_charge';
    case MOULD_OR_TOOLING_CHARGE = 'mould_or_tooling_charge';
    case SAMPLE_CHARGE = 'sample_charge';
    case LOCAL_DELIVERY_TO_PORT_CHARGE = 'local_delivery_to_port_charge';
    case PAYMENT_CURRENCY = 'payment_currency';
    case PAYMENT_TERMS = 'payment_terms';
    case LEAD_TIME_DAYS = 'lead_time_days';
    case MINIMUM_ORDER = 'minimum_order';
    case OTHER_CHARGE = 'other_charge';

    public static function labels(): array
    {
        return [
            'incoterm'                      => __('Incoterm'),
            'packing_charge'                => __('Packing charge'),
            'label_charge'                  => __('Label charge'),
            'barcode_charge'                => __('Barcode charge'),
            'carton_marking_charge'         => __('Carton marking charge'),
            'insert_or_leaflet_charge'      => __('Insert or leaflet charge'),
            'mould_or_tooling_charge'       => __('Mould or tooling charge'),
            'sample_charge'                 => __('Sample charge'),
            'local_delivery_to_port_charge' => __('Local delivery to port charge'),
            'payment_currency'              => __('Payment currency'),
            'payment_terms'                 => __('Payment terms'),
            'lead_time_days'                => __('Lead time in days'),
            'minimum_order'                 => __('Minimum order'),
            'other_charge'                  => __('Other charge'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function isCharge(): bool
    {
        return str_ends_with($this->value, '_charge');
    }
}
