/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 00:11:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

export interface User {
    slug: string
    username: string,
    email: string,
    about: string,
    remember_token: number
    password: string
    created_at: string
    updated_at: string
}
