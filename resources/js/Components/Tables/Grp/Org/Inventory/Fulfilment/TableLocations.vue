<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:31:26 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { Location } from "@/types/location"
import { faBox, faHandHoldingBox, faPallet, faPencil } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faBox, faHandHoldingBox, faPallet, faPencil)

defineProps<{
    data: {
    },
    tab?: string
}>()

function locationRoute(location: Location) {
    switch (route().current()) {
          default:
            return route(
                'grp.org.warehouses.show.fulfilment.locations.show',
              [route().params['organisation'], route().params['warehouse'], location.slug])
    }
}



</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: location }">
            <Link :href="locationRoute(location)" class="primaryLink">
                {{ location.code }}
            </Link>
        </template>

    </Table>
</template>

