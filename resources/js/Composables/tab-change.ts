/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Mar 2023 08:26:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import {router} from "@inertiajs/vue3";

export function useTabChange(tabSlug, currentTab) {
    router.reload(
        {data: {tab: tabSlug},
            only: [tabSlug],
            onSuccess: () => {
                currentTab.value = tabSlug;
            },
        })
}
