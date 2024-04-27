<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Apr 2024 18:34:20 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang='ts'>
import Table from '@/Components/Table/Table.vue'
import {RecurringBill} from "@/types/recurring_bill";


const props = defineProps<{
  data: object
  tab?: string,
}>()


</script>

<template>
  <Table :resource="data" :name="tab" class="mt-5">

  </Table>
</template>