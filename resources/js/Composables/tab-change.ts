/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Mar 2023 08:26:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import { router } from "@inertiajs/vue3"
import { Ref } from 'vue'

export const useTabChange = (tabSlug: string, currentTab: Ref<string>) => {

    // console.log(tabSlug, currentTab.value)
    if (tabSlug === currentTab.value) {
        return
    }

    router.reload(
        {
            data: { tab: tabSlug },
            only: [tabSlug],  // only reload the props with dynamic name tabSlug
            onSuccess: () => {
                currentTab.value = tabSlug;
            },
        }
    )
}
