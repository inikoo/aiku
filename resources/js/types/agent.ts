/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 00:11:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

export interface Agent {
    slug:string,
    code: string,
    owner_type: string,
    owner_id: string
    name: string
    company_name: string
    contact_name: string
    created_at: string
    updated_at: string
    email: string
    phone: string
    location: string[]

}
