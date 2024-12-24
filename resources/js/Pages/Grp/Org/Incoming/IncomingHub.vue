<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 21 Aug 2024 12:57:59 Central Indonesia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"

// defineProps(['title', 'pageHead', 'flatTreeMaps'])
const props = defineProps<{
    title: string
    pageHead: {}
    stats_box: {
        label: string
        value: number
    }[]
}>()


import { library } from '@fortawesome/fontawesome-svg-core'
import { faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt } from '@fal'
import SimpleBox from '@/Components/DataDisplay/SimpleBox.vue'

library.add(faInventory, faWarehouse, faMapSigns, faBox, faBoxesAlt);

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>

    <SimpleBox v-if="stats_box" :data="stats_box" :link="link"/>


</template>
