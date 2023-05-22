<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 22 May 2023 10:35:34 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faLayerGroup, faEnvelope, faPhone, faPersonDolly, faMapMarkerAlt } from '../../../../resources/private/pro-solid-svg-icons';
import { faCopy } from '../../../../resources/private/pro-regular-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(faLayerGroup, faEnvelope, faPhone, faPersonDolly, faCopy, faMapMarkerAlt);

const props = defineProps<{
  data: object,
}>()

const copyText = () => {
  const input = document.getElementById("company-name");
  input.select();
  input.setSelectionRange(0, 99999); // For mobile devices

  try {
    const successful = document.execCommand('copy');
    if (successful) {
      console.log('Copied to clipboard:', input.value);
    } else {
      console.error('Copy to clipboard failed.');
    }
  } catch (err) {
    console.error('Unable to copy to clipboard:', err);
  }
}

console.log(props.data)
</script>

<template layout="App">
  <div class="grid grid-flow-col grid-cols-2 border-y-2 border-gray-200 divide-x-2 divide-gray-200">

    <!-- Section 1 -->
    <div class=" bg-indigo-100/50 ">
      <div class="flex">
        <div class="grid justify-center">
          <img class="rounded object-cover w-40 h-40 shadow" src="https://source.unsplash.com/featured/" alt="">
        </div>
        <div class="pt-4 pl-3">
          <div class="grid font-semibold text-indigo-700">{{ data.contact_name }}</div>
          <div class="grid grid-flow-col text-indigo-700 space-x-1 w-full">
            <input class="font-extrabold text-2xl w-full bg-transparent focus:ring-0 focus:outline-none overflow-visible"
              readonly :value="data.company_name" id="company-name">
            <div class="cursor-pointer px-1 pt-1 grid justify-center text-lg" @click="copyText">
              <FontAwesomeIcon icon="far fa-copy" class="mr-1" aria-hidden="true" />
            </div>
          </div>
          <div class="grid text-sm font-medium text-indigo-500"></div>
          
          <!-- Contact Section -->
          <div class="pt-4 flex flex-col ">
            <!-- <div class="grid justify-center items-center mb-2 bg-gray-100 rounded-full w-16 h-16 p-4 place-self-center">
              <FontAwesomeIcon icon="fas fa-layer-group" class="w-full h-auto text-indigo-700" aria-hidden="true" />
            </div> -->

            <div class="text-lg text-gray-600 pb-2">
              <div class="grid grid-flow-col  justify-start items-center">
                <FontAwesomeIcon icon="fas fa-envelope" class="mr-2" aria-hidden="true" />
                {{ data.email }}
              </div>
              <div class="grid grid-flow-col  justify-start items-center">
                <FontAwesomeIcon icon="fas fa-phone" class="mr-2" aria-hidden="true" />
                {{ data.phone }}
              </div>
              <div class="grid grid-flow-col  justify-start items-center">
                <FontAwesomeIcon icon="fas fa-map-marker-alt" class="mr-2" aria-hidden="true" />
                {{ data.location[2] }}, {{ data.location[1] }}
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Section 2 -->
    <div class="grid items-center px-8">

      <!-- Orders & Purchase -->
      <div class="grid grid-flow-col text-gray-700 space-x-4">
        <div class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-700 grid justify-center hover:bg-indigo-100 ">
          <div class="grid justify-center text-2xl font-extrabold">{{ data.stats.number_purchase_orders }}</div>
          <div class="text-sm text-gray-400">Orders</div>
        </div>
        <div class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-700 grid justify-center hover:bg-indigo-100">
          <div class="grid justify-center text-2xl font-extrabold">{{ data.stats.number_deliveries }}</div>
          <div class="text-sm text-gray-400">Purchase</div>
        </div>
        <div class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-700 grid justify-center hover:bg-indigo-100">
          <div class="grid justify-center text-2xl font-extrabold">{{ data.stats.suppliers_count }}</div>
          <div class="text-sm text-gray-400">Suppliers</div>
        </div>
        <div class="border border-gray-200 rounded-lg shadow-md py-2 text-indigo-700 grid justify-center hover:bg-indigo-100">
          <div class="grid justify-center text-2xl font-extrabold">{{ data.stats.number_purchase_orders_status_settled_cancelled }}</div>
          <div class="text-sm text-gray-400">Cancelled</div>
        </div>
      </div>
    </div>


  </div>
</template>

