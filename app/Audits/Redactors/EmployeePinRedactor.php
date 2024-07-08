<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jul 2024 19:44:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Audits\Redactors;

class EmployeePinRedactor implements \OwenIt\Auditing\Contracts\AttributeRedactor
{
    /**
     * {@inheritdoc}
     */
    public static function redact($value): string
    {
        return '######';
    }
}
