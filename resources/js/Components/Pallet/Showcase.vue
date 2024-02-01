<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Timeline from '@/Components/Timeline/Timeline.vue'
import TablePallets from "@/Components/Tables/TablePallets.vue";

const props = defineProps<{
    data: {
        data: {
            id: Number,
            pallets: Array,
            reference: String,
            state: String,
            timeline: Array
        }
    }
}>()
console.log(props.data.data.pallets)
</script>

<template>
    <div class="py-3 mx-auto px-5 w-full">
        <Timeline :options="data.data.timeline"/>
    </div>
    <TablePallets :data="data" :tab="'pallets'"/>
</template>
