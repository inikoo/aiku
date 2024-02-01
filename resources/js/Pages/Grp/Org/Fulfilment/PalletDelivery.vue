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

const props = defineProps<{
  title: string
  tabs: object
  pallets?: object
  timeline?: object
  history?: object
  pageHead: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const form = useForm({ reference: '' })

const handleFormSubmit = (data: object) => {
  form.post(route(
    data.route.name,
    data.route.parameters
  ), {
    preserveScroll: true,
    onSuccess: () => form.reset('reference'),
  })
}

const component = computed(() => {

  const components = {
    showcase: null,
    pallets: ShowcasePallet,
    history: TableHistories
  };
  return components[currentTab.value];

});

</script>

<template layout="App">
  <Head :title="capitalize(title)" />
  <PageHeading :data="pageHead">
    <template #button-create-pallet="{ action: action }">
      <div class="relative">
        <Popover :width="'w-full'" ref="_popover">
          <template #button>
            <Button :style="action.action.style" :label="action.action.label" :icon="action.action.icon"
              :iconRight="action.action.iconRight" :key="`ActionButton${action.action.label}${action.action.style}`"
              :tooltip="action.action.tooltip" />
          </template>
          <template #content="{ close: closed }">
            <div class="w-[250px]">
              <PureInput v-model="form.reference" placeholder="Reference"></PureInput>
              <p v-if="get(form, ['errors','reference'])" class="mt-2 text-sm text-red-600">
                {{ form.errors.reference }}
              </p>
              <div class="flex justify-end mt-3">
                <Button :style="'save'" :label="'save'" @click="() => handleFormSubmit(action.action)" />
              </div>
            </div>
          </template>
        </Popover>
      </div>
    </template>

  </PageHeading>
  <Timeline :options="timeline" />
  <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
  <component :is="component" :data="props[currentTab]" :timeline="timeline" :tab="currentTab"></component>
</template>
