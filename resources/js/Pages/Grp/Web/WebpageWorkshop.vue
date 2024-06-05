<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import { library } from '@fortawesome/fontawesome-svg-core';
import { ref } from 'vue'
import { faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faCoins, faTimes, faBrowser } from '@fal';
import dataList from './data/blogActivity'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import Button from '@/Components/Elements/Buttons/Button.vue';
import Modal from "@/Components/Utils/Modal.vue"
import BlockList from './Block/BlockList.vue'
import WowsbarBanner from './Block/WowsbarBanner.vue'
import axios from 'axios'

library.add(faCoins, faMoneyCheckAlt, faCashRegister, faFileInvoiceDollar, faTimes, faBrowser);

const props = defineProps<{
    title: string,
    pageHead: PageHeadingTypes,
    webpageID : String,
    websiteState : String,
    webpageState : String,
    isDirty : String,
    pageCode : String,
    imagesUploadRoute : Object,
    publishRoute : Object,
    setAsReadyRoute : Object,
    updateRoute : Object,
    loadRoute : Object
    
}>()

console.log(props)

const openModal = ref(false)
const data = ref([])


const onUpdated = async() =>{
  console.log('masuk')
  try {
			await axios.patch(route(props.updateRoute.name, props.deleteRoute.parameters),data)
			console.log('saved')

		} catch (error: any) {
			console.log('error',error)
		}

} 


const getComponent = (componentName: string) => {
    const components: any = {
        'bannerWowsbar': WowsbarBanner,
    }
    return components[componentName] ?? null
}

const onPickBlock = (e) => {
  data.value.push(e)
  openModal.value = false
  onUpdated()
}

const deleteBlock = (index) =>{
  console.log('mmmm')
  data.value.splice(index,1)
  onUpdated()
}


const setData = ()=>{
  console.log(data.value)
}



</script>

<template>

  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead"></PageHeading>


  <div class="mx-auto px-4 py-4 sm:px-6 lg:px-8 w-full h-screen" >
    <div class="mx-auto grid grid-cols-4 gap-1 lg:mx-0 lg:max-w-none">
      <div class="col-span-3 h-screen">
        <div v-if="data.length == 0"
          class="relative block w-full h-full  border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
          <span class="mt-2 block text-sm font-semibold text-gray-900">You dont have block</span>
        </div>
        <div v-else>
            <div v-for="(activityItem, activityItemIdx) in data" :key="activityItem.id" class="w-full">
              <component :is="getComponent(activityItem['type'])" :key="index" v-bind="activityItem.fieldData" v-model="activityItem.fieldValue"> </component>
            </div>
          </div>
      </div>

      <div class="col-span-1  h-screen">
        <div class=" border-2  bg-gray-200 p-3 h-full">
          <div class="flex justify-between">
            <h2 class="text-sm font-semibold leading-6 text-gray-900">Block List</h2>
            <Button label="Block" type="create" size="xs" @click="() => openModal = true" />
          </div>

          <ul v-if="data.length > 0" role="list" class="mt-2 space-y-1">
            <li v-for="(activityItem, activityItemIdx) in data" :key="activityItem.id" class="gap-x-4 w-full">
              <div class="flex-auto rounded-md p-3 ring-1 ring-inset ring-gray-200 bg-white">
                <div class="flex justify-between gap-x-4">
                  <div class="py-0.5 text-xs leading-5 text-gray-500">
                    <span class="font-medium text-gray-900">{{ activityItem.name }}</span>
                  </div>
                  <div class="flex-none py-0 text-xs leading-5 text-gray-500" @click="()=>deleteBlock(activityItemIdx)"><font-awesome-icon :icon="['fal', 'times']" /></div>
                </div>
              </div>
            </li>
          </ul>

          <div v-else
            class="relative mt-4 block  h-full rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <font-awesome-icon :icon="['fal', 'browser']" class="mx-auto h-12 w-12 text-gray-400" />
            <span class="mt-2 block text-sm font-semibold text-gray-900">You dont have block</span>
          </div>

        </div>
      </div>
    </div>

  </div>


  <Modal :isOpen="openModal" @onClose="openModal = false">
    <BlockList :onPickBlock="onPickBlock" />
  </Modal>

<div @click="setData">see data</div>

</template>