<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 18:37:23 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import SimpleBox from "@/Components/DataDisplay/SimpleBox.vue"
import { routeType } from "@/types/route"
const props = defineProps<{
    data?: {
        box_stats: {
            name: string
            number: number
            route: routeType
            icon: {
                icon: string
                tooltip: string
            }
        }[]
    }
}>()
</script>


<template>
    <SimpleBox v-if="data?.box_stats" :box_stats="data.box_stats" />
    <div v-else>
        Warehouse Showcase
    </div>
</template>
