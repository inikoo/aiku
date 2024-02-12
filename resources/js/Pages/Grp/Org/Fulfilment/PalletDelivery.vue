<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue";
import { computed, ref, reactive } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import TableHistories from "@/Components/Tables/TableHistories.vue";
import ShowcasePallet from '@/Components/Pallet/Showcase.vue'
import Timeline from '@/Components/Timeline/Timeline.vue'
import Popover from '@/Components/Popover.vue';
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue';
import { get, kebabCase } from 'lodash'
import axios from 'axios';
import UploadExcel from '@/Components/Upload/UploadExcel.vue'

const props = defineProps<{
  title: string
  tabs: object
  pallets?: object
  data?: object
  history?: object
  pageHead: object
  updateRoute: object
  uploadRoutes: object
}>()
console.log(props)
let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);
const loading = ref(false)
const timeline = ref({ ...props.data.data })
const dataModal = ref({ isModalOpen: false })
const formAddPallet = useForm({ notes: '', customer_reference: '' })
const formMultiplePallet = useForm({ number_pallets: 1 })

const handleFormSubmitAddPallet = (data: object, closedPopover: Function) => {
  loading.value = true
  formAddPallet.post(route(
    data.route.name,
    data.route.parameters
  ), {
    preserveScroll: true,
    onSuccess: () => {
      closedPopover()
      formAddPallet.reset('notes', 'customer_reference')
      loading.value = false
    },
    onError: (errors) => {
      loading.value = false
      console.error('Error during form submission:', errors);
    },
  })
}

const handleFormSubmitAddMultiplePallet = (data: object, closedPopover: Function) => {
  loading.value = true
  formMultiplePallet.post(route(
    data.route.name,
    data.route.parameters
  ), {
    preserveScroll: true,
    onSuccess: () => {
      closedPopover()
      formMultiplePallet.reset('number_pallets')
      loading.value = false
    },
    onError: (errors) => {
      loading.value = false
      console.error('Error during form submission:', errors);
    },
  })
}

const updateState = async ({ step, options }) => {

  const foundState = options.find((item) => item.key === timeline.value.state)
  const set = step.key == timeline.state || step.index < foundState.index
  if (!set) {
    try {
      const response = await axios.patch(
        route(props.updateRoute.route.name, props.updateRoute.route?.parameters),
        { state: get(step, 'key') }
      )
      console.log(response)
      timeline.value = response.data.data
    } catch (error) {
      console.log('error', error)
    }
  }
}

const handleClick = (action) => {
  const href = action.route?.name ? route(action.route?.name, action.route?.parameters) : action.href?.name ? route(action.href?.name, action.href?.parameters) : '#'
  const method = action.route?.method ?? 'get'
  const data = action.route?.method !== 'get' ? props.dataToSubmit : null
  router[method](
    href,
    data,
    {
      onBefore: (visit) => { loading.value = true },
      onSuccess: (page) => {
        console.log(page)
        if (action.label == 'submit') timeline.value = page.props.data.data
      },
      onFinish: (visit) => { loading.value = false },
    })
};


const component = computed(() => {
  const components = {
    pallets: ShowcasePallet,
    history: TableHistories
  };
  return components[currentTab.value];

});

const onUploadOpen=(action)=>{
  dataModal.value.isModalOpen = true
  dataModal.value.uploadRoutes = action.route
}

</script>

<template layout="App">
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead">
    <template #button-group-add-pallet="{ action: action }">
      <div class="relative">
        <Popover :width="'w-full'" ref="_popover">
          <template #button>
            <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
              :iconRight="action.button.iconRight" :key="`ActionButton${action.button.label}${action.button.style}`"
              :tooltip="action.button.tooltip"
              class="capitalize inline-flex items-center h-full rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0" />
          </template>
          <template #content="{ close: closed }">
            <div class="w-[250px]">
              <span class="text-xs px-1 my-2">Customer Reference : </span>
              <div>
                <PureInput v-model="formAddPallet.customer_reference" placeholder="Reference">
                </PureInput>
                <p v-if="get(formAddPallet, ['errors', 'customer_reference'])" class="mt-2 text-sm text-red-600">
                  {{ formAddPallet.errors.customer_reference }}
                </p>
              </div>

              <div class="mt-3">
                <span class="text-xs px-1 my-2">Notes : </span>
                <textarea
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  v-model="formAddPallet.notes" placeholder="Notes">
              </textarea>
                <p v-if="get(formAddPallet, ['errors', 'notes'])" class="mt-2 text-sm text-red-600">
                  {{ formAddPallet.errors.notes }}
                </p>
              </div>

              <div class="flex justify-end mt-3">
                <Button :style="'save'" :loading="loading" :label="'save'"
                  @click="() => handleFormSubmitAddPallet(action.button, closed)" />
              </div>
            </div>
          </template>
        </Popover>
      </div>
    </template>
    <template #button-group-upload="{ action : action }">
      <Button
         :style="'upload'"
         @click="()=>onUploadOpen(action.button)"
         class="capitalize inline-flex items-center h-full rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0"
      />
    </template>
    <template #button-add-multiple-pallets="{ action: action }">
      <div class="relative">
        <Popover :width="'w-full'" ref="_popover">
          <template #button>
            <Button :style="action.action.style" :label="action.action.label" :icon="action.action.icon"
              :iconRight="action.action.iconRight" :key="`ActionButton${action.action.label}${action.action.style}`"
              :tooltip="action.action.tooltip" />
          </template>
          <template #content="{ close: closed }">
            <div class="w-[250px]">
              <span class="text-xs px-1 my-2">number of pallets : </span>
              <div>
                <PureInput v-model="formMultiplePallet.number_pallets" placeholder="number of pallets" type="number"
                  :min="1">
                </PureInput>
                <p v-if="get(formMultiplePallet, ['errors', 'customer_reference'])" class="mt-2 text-sm text-red-600">
                  {{ formMultiplePallet.errors.number_pallets }}
                </p>
              </div>
              <div class="flex justify-end mt-3">
                <Button :style="'save'" :loading="loading" :label="'save'"
                  @click="() => handleFormSubmitAddMultiplePallet(action.action, closed)" />
              </div>
            </div>
          </template>
        </Popover>
      </div>
    </template>
    <template #button-submit="{ action: action }">
      <div>
        <div v-if="data.data.state == 'in-process' && data.data.number_pallets != 0">
          <Button @click="handleClick(action.action)" :style="action.action.style" :label="action.action.label"
            :icon="action.action.icon" :iconRight="action.action.iconRight" :tooltip="action.action.tooltip"
            :loading="loading" />
        </div>
      </div>
    </template>


  </PageHeading>
  <div class="border-b border-gray-200">
    <Timeline :options="timeline.timeline" :state="timeline.state" @updateButton="updateState" :slidesPerView="5" />
  </div>
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab]" :state="timeline.state" :tab="currentTab"></component>

  <UploadExcel
        :propName="'pallet deliveries'"
        description="Adding Pallet Deliveries"
        :routes="{
            upload: get(dataModal,'uploadRoutes',{}),
            download: props.uploadRoutes.download,
            history: props.uploadRoutes.history
        }"
        :dataModal="dataModal"
    />
</template>
