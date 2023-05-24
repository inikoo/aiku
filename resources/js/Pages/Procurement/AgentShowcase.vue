<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 10:35:34 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faLayerGroup, faEnvelope, faPhone, faMapMarkerAlt, } from '../../../../resources/private/pro-solid-svg-icons';
import { faCopy, faPersonDolly, faBoxFull, faBan, faArrowUp } from '../../../../resources/private/pro-light-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(faLayerGroup, faEnvelope, faPhone, faPersonDolly, faBoxFull, faCopy, faBan, faArrowUp, faMapMarkerAlt);

const props = defineProps<{
  data: object,

}>()

const copyText = (text) => {
  const textarea = document.createElement('textarea')
  textarea.value = text
  document.body.appendChild(textarea)
  textarea.select()
  document.execCommand('copy')
  textarea.remove()
}

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
  <div class="grid text-gray-600  grid-flow-col grid-cols-2  ">

    <!-- Section 1 -->
    <div>
      <div class="grid grid-flow-col w-fit">
        <div class="relative rounded-md h-40 w-40 shadow overflow-hidden grid justify-center m-2">
          <img class="object-fit" src="https://source.unsplash.com/featured/300x300" alt="">
          <div class="absolute bottom-0 w-full h-2/6 bg-gradient-to-t from-gray-900 from-20% "></div>
          <div class="absolute bottom-1.5 left-2 font-semibold text-white">{{ data.contact_name }}</div>
        </div>
        <div class="pl-3 pt-3">
          <div class="grid grid-flow-col  space-x-1 w-full">
            <div class="flex items-center">
              <p class="inline">{{ data.company_name }}
              <span class="group cursor-pointer pl-0.5 pr-1.5 inline justify-center text-xl "
                @click="copyText(data.company_name)">
                <FontAwesomeIcon icon="fal fa-copy" class="text-sm  mr-1 opacity-30 group-hover:opacity-75"
                  aria-hidden="true" />
              </span>
            </p>
            </div>
          </div>

          <!-- Contact Section -->
          <div class="pt-4 flex flex-col text-sm pb-2 space-y-1">
            <div class="grid grid-flow-col justify-start items-center">
              <FontAwesomeIcon icon="fas fa-map-marker-alt" class="mr-2" aria-hidden="true" />
              {{ data.location[2] }}, {{ data.location[1] }}
            </div>
            <div class="grid grid-flow-col justify-start items-center">
              <FontAwesomeIcon icon="fas fa-envelope" class="mr-2" aria-hidden="true" />
              <a :href="`mailto:${data.email}`" class="hover:text-indigo-500">{{ data.email }}</a>
              <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.email)">
                <FontAwesomeIcon icon="fal fa-copy" class="text-sm leading-none  mr-1 opacity-30 group-hover:opacity-75"
                  aria-hidden="true" />
              </div>
            </div>
            <div class="grid grid-flow-col justify-start items-center">
              <FontAwesomeIcon icon="fas fa-phone" class="mr-2" aria-hidden="true" />
              {{ data.phone }}
              <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.phone)">
                <FontAwesomeIcon icon="fal fa-copy" class="text-sm leading-none  mr-1 opacity-30 group-hover:opacity-75"
                  aria-hidden="true" />
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- Section 2: Statistic -->
    <div class="mt-4 grid items-start space-y-1 px-8">
      <div class="grid grid-cols-3 border border-gray-200 rounded shadow-sm">

        <!-- Statistic in loop -->
        <div v-for="statistic in dataStatistic" class="px-2 py-2 flex space-x-2">
          <div class="h-full aspect-square bg-indigo-700 flex items-center justify-center rounded-md">
            <FontAwesomeIcon :icon="statistic.icon" class="text-gray-100 text-lg" aria-hidden="true" />
          </div>
          <div class="grid items-center space-y-0.5">
            <p class="text-[8px] leading-none">{{ statistic.title }}</p>
            <div class="flex space-x-1">
              <p class="text-xl text-indigo-700 font-bold leading-none">{{ statistic.data }}</p>
              <!-- <div class="self-end bg-yellow-300 mb-0.5 px-1 rounded-full flex items-center">
                <FontAwesomeIcon icon="fal fa-arrow-up" class="text-indigo-700 text-[6px]" aria-hidden="true" />
                <div class="text-[7px] leading-[9px]">{{ statistic.databefore }}</div>
              </div> -->
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

