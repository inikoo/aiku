<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 23 Feb 2023 14:32:57 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"


const props = defineProps<{
    title: string
    pageHead: {}
    box_stats: {
        name: string
        number: number
        route: routeType
        icon: {
            icon: string
            tooltip: string
        }
    }[]
}>()

import { library } from '@fortawesome/fontawesome-svg-core'
import { faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt, faSignOut } from '@fal'
import SimpleBox from '@/Components/DataDisplay/SimpleBox.vue'
import { routeType } from '@/types/route'

library.add(faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt, faSignOut);

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <SimpleBox v-if="box_stats" :box_stats="box_stats" />


</template>
