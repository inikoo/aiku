<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import { Order } from "@/types/order"
import type { Links, Meta } from "@/types/Table"
import { useFormatTime } from '@/Composables/useFormatTime'
import Icon from "@/Components/Icon.vue"

import { faSeedling, faPaperPlane, faWarehouse, faHandsHelping, faBox, faTasks, faShippingFast, faTimesCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSeedling, faPaperPlane, faWarehouse, faHandsHelping, faBox, faTasks, faShippingFast, faTimesCircle)

defineProps<{
    data: {
        data: {}[]
        links: Links
        meta: Meta
    },
    tab?: string
}>()


function purgeRoute(purge: {}) {
    console.log(route().current())
    switch (route().current()) {
        case "grp.overview.ordering.purges.index":
            return route(
                "grp.org.shops.show.ordering.purges.show",
                [purge.organisation_name, purge.shop_name, purge.id])
        case "grp.org.shops.show.ordering.purges.index":
            return route(
                "grp.org.shops.show.ordering.purges.show",
                [route().params["organisation"], route().params["shop"], purge.id])
        default:
            return null
    }
}


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(scheduled_at)="{ item: purge }">
            <Link :href="purgeRoute(purge)" class="primaryLink">
                {{ purge["scheduled_at"] }}
            </Link>
        </template>
        <!-- <template #cell(date)="{ item: order }">
            {{ useFormatTime(order.date, {formatTime: 'ddmy'}) }}
        </template> -->
    </Table>
</template>
