<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 10:35:34 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import ShowcaseStatistic from '@/Components/ShowcaseStatistic.vue'
import ShowcaseProfile from '@/Components/ShowcaseProfile.vue'

const props = defineProps<{
  data: object,

}>()

const dataStatistic = [
  {
    title: 'Orders',
    data: props.data.stats.number_purchase_orders,
    icon: 'fal fa-box-full',
    databefore: '164',
  },
  {
    title: 'Deliveries',
    data: props.data.stats.number_deliveries,
    icon: 'fal fa-truck',
    databefore: '0',
  },
  {
    title: 'Suppliers',
    data: props.data.stats.suppliers_count,
    icon: 'fal fa-person-dolly',
    databefore: '88',
  },
  {
    title: 'Cancelled',
    data: props.data.stats.number_purchase_orders_status_settled_cancelled,
    icon: 'fal fa-ban',
    databefore: '20',
  },
]

</script>

<template >
  <div class="grid text-gray-600  grid-flow-col grid-cols-2">

    <!-- Section 1 -->
    <div class="pl-2">
      <ShowcaseProfile :data="data"/>
    </div>

    <!-- Section 2: Statistic -->
    <div class="mt-4 px-4">
      <ShowcaseStatistic />
    </div>


  </div>
</template>

