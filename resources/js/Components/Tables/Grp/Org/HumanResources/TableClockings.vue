<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import Table from '@/Components/Table/Table.vue';
import { useFormatTime } from "@/Composables/useFormatTime";

const props = defineProps<{
    data: object,
    tab?: string
}>()

console.log(props.data)

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(media_slug)="{ item }">
        <Link :href="route('grp.media.show', item.media_slug)" class="primaryLink">
          {{ item.media_slug }}
        </Link>
      </template>
      <template #cell(clocked_at)="{ item }">
        <div class="text-gray-500">
          {{ useFormatTime(item.clocked_at, {formatTime: 'hms'}) }}
        </div>
      </template>
    </Table>
</template>


