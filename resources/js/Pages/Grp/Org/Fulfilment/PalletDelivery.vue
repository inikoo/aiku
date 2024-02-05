<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue";
import { computed, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import TableHistories from "@/Components/Tables/TableHistories.vue";
import ShowcasePallet from '@/Components/Pallet/Showcase.vue'
import Timeline from '@/Components/Timeline/Timeline.vue'
import Popover from '@/Components/Popover.vue';
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue';
import { get } from 'lodash'
import axios from 'axios';

const props = defineProps<{
  title: string
  tabs: object
  pallets?: object
  data?: object
  history?: object
  pageHead: object
  updateRoute: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);
const loading = ref(false)
const timeline = ref({...props.data.data})

const formAddPallet = useForm({ notes: '', reference: ''})
const formMultiplePallet = useForm({ number_pallets: 1 })

const handleFormSubmitAddPallet = (data: object, closedPopover : Function ) => {
  loading.value = true
  formAddPallet.post(route(
    data.route.name,
    data.route.parameters
  ), {
    preserveScroll: true,
    onSuccess: () => {
      closedPopover()
      formAddPallet.reset('notes','reference')
      loading.value = false
    },
    onError: (errors) => {
    loading.value = false
    console.error('Error during form submission:', errors);
  },
  })
}

const handleFormSubmitAddMultiplePallet = (data: object, closedPopover : Function ) => {
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

const component = computed(() => {

  const components = {
    pallets: ShowcasePallet,
    history: TableHistories
  };
  return components[currentTab.value];

});

const updateState = async ( data : object ) => {
  try {
        const response = await axios.patch(
            route(props.updateRoute.route.name, props.updateRoute.route?.parameters),
            { state : get(data,'key') }
        );
      timeline.value =  response.data.data
    } catch (error) {
        console.log('error', error)
    }
}

</script>

<template layout="App">
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead">
    <template #button-add-pallet="{ action: action }">
      <div class="relative">
        <Popover :width="'w-full'" ref="_popover">
          <template #button>
            <Button :style="action.action.style" :label="action.action.label" :icon="action.action.icon"
              :iconRight="action.action.iconRight" :key="`ActionButton${action.action.label}${action.action.style}`"
              :tooltip="action.action.tooltip" />
          </template>
          <template #content="{ close: closed }">
            <div class="w-[250px]">
              <span class="text-xs px-1 my-2">Reference : </span>
            <div>
              <PureInput
                 v-model="formAddPallet.reference"
                 placeholder="Reference"
              >
              </PureInput>
              <p v-if="get(formAddPallet, ['errors','reference'])" class="mt-2 text-sm text-red-600">
                {{ formAddPallet.errors.reference }}
              </p>
            </div>

            <div class="mt-3">
            <span class="text-xs px-1 my-2">Notes : </span>
              <textarea
                 class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                 v-model="formAddPallet.notes"
                 placeholder="Notes"
              >
              </textarea>
              <p v-if="get(formAddPallet, ['errors','notes'])" class="mt-2 text-sm text-red-600">
                {{ formAddPallet.errors.notes }}
              </p>
            </div>

              <div class="flex justify-end mt-3">
                <Button :style="'save'" :loading="loading" :label="'save'" @click="() => handleFormSubmitAddPallet( action.action, closed )" />
              </div>
            </div>
          </template>
        </Popover>
      </div>
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
              <PureInput
                 v-model="formMultiplePallet.number_pallets"
                 placeholder="number of pallets"
                 type="number"
                 :min="1"
              >
              </PureInput>
              <p v-if="get(formMultiplePallet, ['errors','reference'])" class="mt-2 text-sm text-red-600">
                {{ formMultiplePallet.errors.number_pallets }}
              </p>
            </div>
              <div class="flex justify-end mt-3">
                <Button :style="'save'" :loading="loading" :label="'save'" @click="() => handleFormSubmitAddMultiplePallet( action.action, closed )" />
              </div>
            </div>
          </template>
        </Popover>
      </div>
    </template>

  </PageHeading>
  <div class="border-b border-gray-200">
    <Timeline :options="timeline.timeline" :state="timeline.state" @updateButton="updateState" :slidesPerView="5"/>
  </div>
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab]" :timeline="timeline" :tab="currentTab"></component>
</template>
