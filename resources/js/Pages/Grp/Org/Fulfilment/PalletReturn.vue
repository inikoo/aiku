<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from 'vue'
import type { Component } from 'vue'
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import TablePalletReturn from "@/Components/PalletReturn/tablePalletReturn.vue"
import TablePalletReturnsDelivery from "@/Components/Tables/Grp/Org/Fulfilment/TablePalletReturnPallets.vue"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'
import palletReturnDescriptor from "@/Components/PalletReturn/Descriptor/PalletReturn"
import Tag from "@/Components/Tag.vue"
import { BoxStats, PDRNotes, PalletReturn } from '@/types/Pallet'
import BoxStatsPalletReturn from '@/Pages/Grp/Org/Fulfilment/Return/BoxStatsPalletReturn.vue'

import { faIdCardAlt, faUser, faBuilding, faEnvelope, faPhone, faMapMarkerAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { trans } from "laravel-vue-i18n"
import TableServices from "@/Components/Tables/Grp/Org/Fulfilment/TableServices.vue"
import TablePhysicalGoods from "@/Components/Tables/Grp/Org/Fulfilment/TablePhysicalGoods.vue"
import { get } from "lodash"
import PureInput from "@/Components/Pure/PureInput.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import Popover from "@/Components/Popover.vue"
import TableStoredItems from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItems.vue"
import { Tabs as TSTabs } from "@/types/Tabs"

library.add(faIdCardAlt, faUser, faBuilding, faEnvelope, faPhone, faMapMarkerAlt )

const props = defineProps<{
    title: string
    tabs: TSTabs
    pallets?: {}
    stored_items?: {}
    services?: {}
    service_lists?: {}
    physical_good_lists?: {}
    physical_goods?: {}
    data: {
        data: PalletReturn
    }
    history?: {}
    pageHead: PageHeadingTypes
    updateRoute: routeType
    uploadRoutes: routeType
    palletRoute: {
        index: routeType,
        store: routeType
    }
    box_stats: BoxStats
    notes_data: PDRNotes[]
}>()

// console.log('qwewqewq', props.data)

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const timeline = ref({ ...props.data?.data })
const openModal = ref(false)
const loading = ref(false)

const formAddService = useForm({ service_id: '', quantity: 1 })
const formAddPhysicalGood = useForm({ outer_id: '', quantity: 1 })

const component = computed(() => {
    const components: Component = {
        pallets: TablePalletReturnsDelivery,
        stored_items: TableStoredItems,
        services: TableServices,
        physical_goods: TablePhysicalGoods,
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


// Method: Add single service
const handleFormSubmitAddService = (data: {}, closedPopover: Function) => {
    loading.value = true
    formAddService.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formAddService.reset('quantity', 'service_id')
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

// Method: Add single service
const handleFormSubmitAddPhysicalGood = (data: {}, closedPopover: Function) => {
    loading.value = true
    formAddPhysicalGood.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formAddPhysicalGood.reset('quantity', 'outer_id')
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-group-add-pallet="{ action }">
            <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
                :iconRight="action.button.iconRight" :key="`ActionButton${action.button.label}${action.button.style}`"
                :tooltip="action.button.tooltip" @click="() => (openModal = true)" />
        </template>

        <!-- Button: Add service (single) -->
        <template #button-group-add-service="{ action }">
            <div class="relative" v-if="currentTab === 'services'">
                <Popover width="w-full">
                    <template #button>
                        <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
                                :key="`ActionButton${action.button.label}${action.button.style}`"
                                :tooltip="action.button.tooltip" class="rounded-l-none rounded-r border-none " />
                    </template>
                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <span class="text-xs px-1 my-2">{{ trans('Services') }}: </span>
                            <div class="">
                                <PureMultiselect v-model="formAddService.service_id" autofocus placeholder="Services" :options="props.service_lists"
                                                 label="name"
                                                 valueProp="id"
                                                 @keydown.enter="() => handleFormSubmitAddService(action.button, closed)" />
                                <p v-if="get(formAddService, ['errors', 'service_id'])"
                                   class="mt-2 text-sm text-red-500">
                                    {{ formAddService.errors.service_id }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Qty') }}: </span>
                                <PureInput v-model="formAddService.quantity" placeholder="Qty"
                                           @keydown.enter="() => handleFormSubmitAddService(action.button, closed)" />
                                <p v-if="get(formAddService, ['errors', 'quantity'])"
                                   class="mt-2 text-sm text-red-600">
                                    {{ formAddService.errors.quantity }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button :style="'save'" :loading="loading" :label="'save'"
                                        @click="() => handleFormSubmitAddService(action.button, closed)" />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
            <div v-else></div>
        </template>

        <!-- Button: Add physical good (single) -->
        <template #button-group-add-physical-good="{ action }">
            <div class="relative" v-if="currentTab === 'physical_goods'">
                <Popover width="w-full">
                    <template #button>
                        <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
                                :key="`ActionButton${action.button.label}${action.button.style}`"
                                :tooltip="action.button.tooltip" class="rounded-l-none rounded-r border-none " />
                    </template>
                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <span class="text-xs px-1 my-2">{{ trans('Physical Goods') }}: </span>
                            <div>
                                <PureMultiselect
                                    v-model="formAddPhysicalGood.outer_id"
                                    autofocus
                                    placeholder="Physical Goods"
                                    :options="props.physical_good_lists"
                                    label="name"
                                    valueProp="id"
                                    @keydown.enter="() => handleFormSubmitAddPallet(action.button, closed)" />
                                <p v-if="get(formAddPhysicalGood, ['errors', 'outer_id'])"
                                   class="mt-2 text-sm text-red-600">
                                    {{ formAddPhysicalGood.errors.outer_id }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Qty') }}: </span>
                                <PureInput v-model="formAddPhysicalGood.quantity" placeholder="Qty"
                                           @keydown.enter="() => handleFormSubmitAddPallet(action.button, closed)" />
                                <p v-if="get(formAddPhysicalGood, ['errors', 'quantity'])"
                                   class="mt-2 text-sm text-red-600">
                                    {{ formAddPhysicalGood.errors.quantity }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button :style="'save'" :loading="loading" :label="'save'"
                                        @click="() => handleFormSubmitAddPhysicalGood(action.button, closed)" />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
            <div v-else></div>
        </template>
    </PageHeading>

    <!-- Section: Note -->
    <div class="h-fit lg:max-h-64 w-full flex lg:justify-center border-b border-gray-300">
        <BoxNote v-for="(note, index) in notes_data" :key="index+note.label" :noteData="note" :updateRoute="updateRoute" />
    </div>

    <!-- Section: Timeline -->
    <div class="border-b border-gray-200">
        <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="Object.entries(timeline.timeline).length" />
    </div>

    <!-- Section: Box Stats -->
    <BoxStatsPalletReturn :dataPalletReturn="data.data" :boxStats="box_stats" />

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :state="timeline.state" :key="timeline.state" :tab="currentTab" />

    <Modal :isOpen="openModal" @onClose="openModal = false">
        <div class="">
            <TablePalletReturn
				:dataRoute="palletRoute.index"
                :saveRoute="palletRoute.store"
				@onClose="() => openModal = false"
				:descriptor="palletReturnDescriptor"
			>
                <template #column-stored_items="{data}">
                    <!-- {{ data.columnData.stored_items }} -->
                    <div class="flex gap-x-1 flex-wrap">
                        <template v-if="data.columnData.stored_items.length">
                            <Tag v-for="item of data.columnData.stored_items"
                                :label="`${item.reference} (${item.quantity})`"
                                :closeButton="false"
                                :stringToColor="true">
                                <template #label>
                                    <div class="whitespace-nowrap text-xs">
                                        {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                                    </div>
                                </template>
                            </Tag>
                        </template>
                        <span v-else class="text-xs text-gray-400 italic">Have no stored items.</span>
                    </div>
                </template>

            </TablePalletReturn>
        </div>
    </Modal>
</template>
