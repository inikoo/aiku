<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
  import { Head, useForm, router } from "@inertiajs/vue3"
  import PageHeading from "@/Components/Headings/PageHeading.vue"
  import { capitalize } from "@/Composables/capitalize"
  import Tabs from "@/Components/Navigation/Tabs.vue"
  import { computed, ref, watch } from "vue"
  import { useTabChange } from "@/Composables/tab-change"
  import TableHistories from "@/Components/Tables/TableHistories.vue"
  import Timeline from "@/Components/Utils/Timeline.vue"
  import Button from "@/Components/Elements/Buttons/Button.vue"
  import Modal from "@/Components/Utils/Modal.vue"
  import TablePalletReturn from "@/Components/PalletReturn/tablePalletReturn.vue"
  import TablePalletReturnsDelivery from "@/Components/Tables/TablePalletReturnsDelivery.vue"
  import { routeType } from '@/types/route'
  import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
  import palletReturnDescriptor from "@/Components/PalletReturn/Descriptor/PalletReturn.ts"
  
  const props = defineProps<{
      title: string
      tabs: object
      pallets?: object
      data?: object
      history?: object
      pageHead: PageHeadingTypes
      updateRoute: routeType
      uploadRoutes: routeType
      palletRoute: {
          index: routeType,
          store: routeType
      }
  }>()
  let currentTab = ref(props.tabs.current)
  const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab)
  const timeline = ref({ ...props.data.data })
  const openModal = ref(false)
  
  const component = computed(() => {
      const components = {
          pallets: TablePalletReturnsDelivery,
          history: TableHistories,
      }
      return components[currentTab.value]
  })
  
  
  watch(
      props,
      (newValue) => {
          timeline.value = newValue.data.data
      },
      { deep: true }
  )
  
  </script>
  
  <template>
  
      <Head :title="capitalize(title)" />
      <PageHeading :data="pageHead">
          <template #button-group-add-pallet="{ action: action }">
              <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
                  :iconRight="action.button.iconRight" :key="`ActionButton${action.button.label}${action.button.style}`"
                  :tooltip="action.button.tooltip" @click="() => (openModal = true)" />
          </template>
      </PageHeading>
  
      <div class="border-b border-gray-200"> 
          <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="Object.entries(timeline.timeline).length" />
      </div>
  
      <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
      <component :is="component" :data="props[currentTab]" :state="timeline.state" :tab="currentTab" />
  
      <Modal :isOpen="openModal" @onClose="openModal = false">
          <div class="min-h-72 max-h-96 px-2 overflow-auto">
              <TablePalletReturn 
                  :dataRoute="palletRoute.index"
                  :saveRoute="palletRoute.store"
                  @onClose="() => openModal = false" 
                  :descriptor="palletReturnDescriptor"
              />
          </div>
      </Modal>
  </template>
  