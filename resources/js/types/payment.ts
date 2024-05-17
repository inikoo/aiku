/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

export interface Payment {
    id: number
    payment_service_providers_slug: string
    payment_accounts_slug: string
    status: string
    date: string
    reference: string,
}
