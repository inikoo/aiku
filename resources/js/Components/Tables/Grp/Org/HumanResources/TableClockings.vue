<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import { useFormatTime } from "@/Composables/useFormatTime";

const props = defineProps<{
    data: object,
    tab?: string
}>()

console.log(props.data)
console.log(props.tab)

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(clocked_at)="{ item }">
        <div class="text-gray-500">
          {{ useFormatTime(item.clocked_at, {formatTime: 'hms'}) }}
        </div>
      </template>
    </Table>
</template>


