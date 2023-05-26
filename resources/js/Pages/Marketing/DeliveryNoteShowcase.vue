<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Thu, 25 May 2023 15:03:05 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import ShowcaseStats from '@/Components/ShowcaseStats.vue'
import ShowcaseContactCard from "@/Components/ShowcaseContactCard.vue";

const props = defineProps<{
  data: {
    contactCard: {
    },
    stats: [{
      label: string,
      value: number,
    }],
  },

}>()
</script>

<template >
  <div class="grid text-gray-600  grid-flow-col grid-cols-2 px-4 pt-4">
    <!-- Section 1 -->
    <div class="">
      <ShowcaseContactCard :data="data.contactCard" />
    </div>

    <!-- Section 2: Statistic -->
    <div class="pl-6">
      <ShowcaseStats :data="data.stats" />
    </div>


  </div>
</template>

