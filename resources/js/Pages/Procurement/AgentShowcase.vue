<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 10:35:34 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faLayerGroup, faEnvelope, faPhone, faPersonDolly, faMapMarkerAlt } from '../../../../resources/private/pro-solid-svg-icons';
import { faCopy } from '../../../../resources/private/pro-light-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(faLayerGroup, faEnvelope, faPhone, faPersonDolly, faCopy, faMapMarkerAlt);

const props = defineProps<{
    data: object,

}>()

const copyText2 = (abc) => {
  const textarea = document.createElement('textarea')
  textarea.value = abc
  document.body.appendChild(textarea)
  textarea.select()
  document.execCommand('copy')
  textarea.remove()
}

</script>

<template >
  <div class="grid text-gray-600  grid-flow-col grid-cols-2  ">

    <!-- Section 1 -->
    <div >
      <div class="flex items-center">
        <div class="relative rounded w-40 h-40 shadow grid justify-center m-2">
          <img class=" object-contain" src="https://source.unsplash.com/featured/300x300" alt="">
          <div class="absolute bottom-0 w-full h-2/6 bg-gradient-to-t from-gray-900 from-20% "></div>
          <div class="absolute bottom-1.5 left-2 font-semibold text-white">{{ data.contact_name }}</div>
        </div>
        <div class="pt-0 pl-3">
          <div class="grid grid-flow-col  space-x-1 w-full">
            <div class="">
              <p class="inline">{{ data.company_name }}</p>
              <div class="cursor-pointer px-1.5 pt-1 inline justify-center text-xl "
                @click="copyText2(data.company_name)">
                <FontAwesomeIcon icon="fal fa-copy" class="text-base  mr-1 opacity-20 hover:opacity-75" aria-hidden="true" />
              </div>
            </div>
          </div>

          <!-- Contact Section -->
          <div class="pt-4 flex flex-col text-sm pb-2">
            <div class="grid grid-flow-col justify-start items-center">
              <FontAwesomeIcon icon="fas fa-envelope" class="mr-2" aria-hidden="true" />
              {{ data.email }}
            </div>
            <div class="grid grid-flow-col justify-start items-center">
              <FontAwesomeIcon icon="fas fa-phone" class="mr-2" aria-hidden="true" />
              {{ data.phone }}
            </div>
            <div class="grid grid-flow-col justify-start items-center">
              <FontAwesomeIcon icon="fas fa-map-marker-alt" class="mr-2" aria-hidden="true" />
              {{ data.location[2] }}, {{ data.location[1] }}
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Section 2 -->
    <div class="grid items-center px-8">

      <!-- Orders & Purchase -->
      <div class="grid grid-flow-col grid-cols-4 text-gray-700 space-x-4">
        <div
          class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-600 grid justify-center hover:bg-slate-100 ">
          <div class="grid justify-center text-2xl font-extrabold">{{ data.stats.number_purchase_orders }}</div>
          <div class="text-sm text-gray-400">Orders</div>
        </div>
        <div
          class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-600 grid justify-center hover:bg-slate-100">
          <div class="grid justify-center text-2xl font-extrabold">{{ data.stats.number_deliveries }}</div>
          <div class="text-sm text-gray-400">Purchase</div>
        </div>
        <div
          class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-600 grid justify-center hover:bg-slate-100">
          <div class="grid justify-center text-2xl font-extrabold">{{ data.stats.suppliers_count }}</div>
          <div class="text-sm text-gray-400">Suppliers</div>
        </div>
        <div
          class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-600 grid justify-center hover:bg-slate-100">
          <div class="grid justify-center text-2xl font-extrabold">{{
            data.stats.number_purchase_orders_status_settled_cancelled }}</div>
          <div class="text-sm text-gray-400">Cancelled</div>
        </div>
      </div>
    </div>


  </div>
</template>

