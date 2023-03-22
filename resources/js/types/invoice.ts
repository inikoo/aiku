/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 00:11:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

export interface Invoice {
    slug:string,
    number: string,
    customer_id: string,
    order_id: string
    type: string
    currency_id: string
    total: number
    created_at: string
    updated_at: string
    net: number
    payment: number

}
