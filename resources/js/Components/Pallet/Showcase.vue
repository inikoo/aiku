<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import TablePallets from "@/Components/Tables/TablePallets.vue";

const props = defineProps<{
    data: {
        data: {
            id: Number,
            pallets: Array,
            reference: String,
            state: String,
        }
    }
    timeline: object
}>()
</script>

<template>
        <TablePallets :data="data" :tab="'pallets'"></TablePallets>               
</template>
