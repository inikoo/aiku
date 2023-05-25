<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 10:35:34 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import ShowcaseStatistic from '@/Components/ShowcaseStatistic.vue'
import ShowcaseContactCard from "@/Components/ShowcaseContactCard.vue";

const props = defineProps<{
  data: {
    contactCard: {
      company?: string
      contact?: string
      email?: string
      phone?: string
      address?: string
      photo?: string
    },
    stats: [{
      label: string,
      value: number,
    }],
  },

}>()
</script>
  
<template >
  <div class="grid text-gray-600  grid-flow-col grid-cols-2">
    <!-- Section 1 -->
    <div class="pl-2">
      <ShowcaseContactCard :data="data.contactCard" />
    </div>

    <!-- Section 2: Statistic -->
    <div class="mt-4 px-4">
      <ShowcaseStatistic :data="data.stats" />
    </div>


  </div>
</template>
  
  