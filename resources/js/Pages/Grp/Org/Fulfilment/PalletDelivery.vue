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

const form = useForm({ notes: '', reference: ''})

const handleFormSubmit = (data: object, closedPopover : Function ) => {
  loading.value = true
  form.post(route(
    data.route.name,
    data.route.parameters
  ), {
    preserveScroll: true,
    onSuccess: () => {
      closedPopover()
      form.reset('notes','reference')
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
      props.data.data =  response.data.data
    } catch (error) {
        console.log('error', error)
    }
}

console.log('porps',props)

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
              <span class="text-xs px-1 my-2">Reference : </span><span class=" decoration-sky-500 text-xs">(optional)</span>
            <div>
              <PureInput 
                 v-model="form.reference" 
                 placeholder="Reference"
              >
              </PureInput>
              <p v-if="get(form, ['errors','reference'])" class="mt-2 text-sm text-red-600">
                {{ form.errors.reference }}
              </p>
            </div>

            <div class="mt-3">
            <span class="text-xs px-1 my-2">Notes : </span>
              <textarea  
                 class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                 v-model="form.notes" 
                 placeholder="Notes"
              >
              </textarea>
              <p v-if="get(form, ['errors','notes'])" class="mt-2 text-sm text-red-600">
                {{ form.errors.notes }}
              </p>
            </div>
             
              <div class="flex justify-end mt-3">
                <Button :style="'save'" :loading="loading" :label="'save'" @click="() => handleFormSubmit( action.action, closed )" />
              </div>
            </div>
          </template>
        </Popover>
      </div>
    </template>

  </PageHeading>
  <Timeline :options="data.data.timeline" :state="data.data.state" @updateButton="updateState"/>
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab]" :timeline="timeline" :tab="currentTab"></component>
</template>
