/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 10:25:09 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

export interface OrgSupplierProduct {
    composition:string,
    slug: string,
    agent_slug: string,
    supplier_slug: string,
    current_historic_supplier_product_id: string,
    supplier_id: number
    agent_id: number
    state: string
    stock_quantity_status: string
    created_at: string
    updated_at: string
    code: string
    name: string
    description: string
    units_per_pack: number
    units_per_carton: string

}
