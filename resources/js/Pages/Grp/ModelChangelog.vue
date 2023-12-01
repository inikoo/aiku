<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
const props = defineProps<{
    data: object,
}>()


</script>

<template>
    <Table v-if="data.records"  :resource="data.records" class="mt-5"></Table>
</template>


