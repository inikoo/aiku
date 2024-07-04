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
import { computed, ref, watch } from "vue"
import type { Component } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import TablePalletReturn from "@/Components/PalletReturn/tablePalletReturn.vue"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import palletReturnDescriptor from "@/Components/PalletReturn/Descriptor/PalletReturn"
import { Tabs as TSTabs } from '@/types/Tabs'
import { Action } from '@/types/Action'


import TablePalletReturnPallets from "@/Components/Tables/Grp/Org/Fulfilment/TablePalletReturnPallets.vue"
import TableServices from "@/Components/Tables/Grp/Org/Fulfilment/TableServices.vue"
import TablePhysicalGoods from "@/Components/Tables/Grp/Org/Fulfilment/TablePhysicalGoods.vue"
import TableStoredItems from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItems.vue"

import Popover from "@/Components/Popover.vue"
import PureInput from "@/Components/Pure/PureInput.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSpinnerThird } from '@fad'
import { faCube, faConciergeBell } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCube, faConciergeBell, faSpinnerThird)

// import '@/Composables/Icon/PalletStateEnum.ts'
// import '@/Composables/Icon/PalletDeliveryStateEnum.ts'
// import '@/Composables/Icon/PalletReturnStateEnum.ts'
import { trans } from 'laravel-vue-i18n'
import { get } from 'lodash'
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"

const props = defineProps<{
	title: string
	tabs: TSTabs
	data?: {}
	history?: {}
	pageHead: PageHeadingTypes
	updateRoute: routeType
	uploadRoutes: routeType
    palletRoute : {
		index : routeType,
		store : routeType
	}

	pallets?: {}
    stored_items?: {}
    services?: {}
    service_list_route: routeType

    physical_goods?: {}
    physical_good_list_route?: routeType
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const timeline = ref({ ...props.data?.data })
const openModal = ref(false)
const isLoading = ref<string | boolean>(false)
const isLoadingData = ref<string | boolean>(false)

const formAddService = useForm({ service_id: '', quantity: 1 })
const formAddPhysicalGood = useForm({ outer_id: '', quantity: 1 })

const component = computed(() => {
	const components: Component = {
		pallets: TablePalletReturnPallets,
        stored_items: TableStoredItems,
        services: TableServices,
        physical_goods: TablePhysicalGoods,
		history: TableHistories,
	}
	return components[currentTab.value]
})


// Tabs: Services
const dataServiceList = ref([])
const onOpenModalAddService = async () => {
    isLoadingData.value = 'addService'
    try {
        const xxx = await axios.get(
            route(props.service_list_route.name, props.service_list_route.parameters)
        )
        dataServiceList.value = xxx.data.data
    } catch (error) {
        
    }
    isLoadingData.value = false
}
const onSubmitAddService = (data: Action, closedPopover: Function) => {
    isLoading.value = 'addService'
    formAddService.post(
        route( data.route?.name, data.route?.parameters),
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                formAddService.reset('quantity', 'service_id')
                isLoading.value = false
            },
            onError: (errors) => {
                isLoading.value = false
                console.error('Error during form submission:', errors)
            },
        }
    )
}


// Tabs: Physical Goods
const dataPGoodList = ref([])
const onOpenModalAddPGood = async () => {
    isLoadingData.value = 'addPGood'
    try {
        const xxx = await axios.get(
            route(props.physical_good_list_route.name, props.physical_good_list_route.parameters)
        )
        dataPGoodList.value = xxx.data.data
    } catch (error) {
        notify({
            title: 'Something went wrong.',
            text: 'Failed to fetch Physical Good list',
            type: 'error',
        })
    }
    isLoadingData.value = false
}
const onSubmitAddPhysicalGood = (data: Action, closedPopover: Function) => {
    isLoading.value = 'addPGood'
    formAddPhysicalGood.post(
        route( data.route?.name, data.route?.parameters ),
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                formAddPhysicalGood.reset('quantity', 'outer_id')
                isLoading.value = false
            },
            onError: (errors) => {
                isLoading.value = false
                console.error('Error during form submission:', errors)
            },
        }
    )
}

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
        <template #button-group-add-pallet="{ action }">
            <Button
                v-if="currentTab == 'pallets'"
                :style="action.style"
                :label="action.label"
                :icon="action.icon"
                :iconRight="action.iconRight"
                :key="`ActionButton${action.label}${action.style}`"
                :tooltip="action.tooltip"
                @click="() => openModal = true"
            />
            <div v-else></div>
        </template>

        <!-- Button: Add services -->
        <template #button-group-add-service="{ action }">
            <div class="relative" v-if="currentTab === 'services'">
                <Popover width="w-full">
                    <template #button>
                        <Button
                            @click="() => onOpenModalAddService()"
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :disabled="isLoadingData == 'addService'"
                            :tooltip="action.tooltip"
                            :key="`ActionButton${action.label}${action.style}${isLoadingData == 'addService'}`"
                        />
                    </template>

                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <span class="text-xs px-1 my-2">{{ trans('Services') }}: </span>
                            <div class="">
                                <PureMultiselect
                                    v-model="formAddService.service_id"
                                    autofocus
                                    caret
                                    required
                                    placeholder="Services"
                                    :options="dataServiceList"
                                    label="name"
                                    valueProp="id"
                                    @keydown.enter="() => onSubmitAddService(action, closed)"
                                />
                                <p v-if="get(formAddService, ['errors', 'service_id'])"
                                    class="mt-2 text-sm text-red-500">
                                    {{ formAddService.errors.service_id }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Quantity') }}: </span>
                                <PureInput
                                    v-model="formAddService.quantity"
                                    placeholder="Quantity"
                                    @keydown.enter="() => onSubmitAddService(action, closed)"
                                />
                                <p v-if="get(formAddService, ['errors', 'quantity'])" class="mt-2 text-sm text-red-500">
                                    {{ formAddService.errors.quantity }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button
                                    :key="'submitAddService' + isLoading"
                                    :style="'save'"
                                    :loading="isLoading === 'addService'"
                                    full
                                    :label="'save'"
                                    @click="() => onSubmitAddService(action, closed)"
                                />
                            </div>
                            
                            <!-- Loading: fetching service list -->
                            <div v-if="isLoadingData === 'addService'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                                <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width aria-hidden='true' />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
            <div v-else />
        </template>


        <!-- Button: Add physical good (single) -->
        <template #button-group-add-physical-good="{ action }">
            <div class="relative" v-if="currentTab === 'physical_goods'">
                <Popover width="w-full">
                    <template #button>
                        <Button
                            @click="() => onOpenModalAddPGood()"
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
                        />
                    </template>
                    
                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <span class="text-xs px-1 my-2">{{ trans('Physical Goods') }}: </span>
                            <div>
                                <PureMultiselect
                                    v-model="formAddPhysicalGood.outer_id"
                                    autofocus
                                    caret
                                    required
                                    placeholder="Physical Goods"
                                    :options="dataPGoodList"
                                    label="name"
                                    valueProp="id"
                                />
                                <p v-if="get(formAddPhysicalGood, ['errors', 'outer_id'])"
                                    class="mt-2 text-sm text-red-500">
                                    {{ formAddPhysicalGood.errors.outer_id }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Quantity') }}: </span>
                                <PureInput
                                    v-model="formAddPhysicalGood.quantity"
                                    placeholder="Quantity"
                                />
                                <p v-if="get(formAddPhysicalGood, ['errors', 'quantity'])"
                                    class="mt-2 text-sm text-red-500">
                                    {{ formAddPhysicalGood.errors.quantity }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button
                                    :style="'save'"
                                    :loading="isLoading === 'addPGood'"
                                    label="save"
                                    @click="() => onSubmitAddPhysicalGood(action, closed)"
                                />
                            </div>
                            
                            <!-- Loading: fetching service list -->
                            <div v-if="isLoadingData === 'addService'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                                <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width aria-hidden='true' />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
            <div v-else />
        </template>
    </PageHeading>

    <div class="border-b border-gray-200">
        <Timeline
            :options="timeline.timeline"
            :state="timeline.state"
            :slidesPerView="Object.entries(timeline.timeline).length"
        />
    </div>

    <!-- Todo -->
    <!-- Box: Notes -->
    <!-- <BoxStatPallet class="pb-2 pt-6 px-2" tooltip="Notes">
        <div class="h-full w-full px-2 flex flex-col items-center">
            <PureTextarea full :placeholder="trans('Enter notes for this pallet return')" />
        </div>
    </BoxStatPallet> -->

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />

    <component :is="component" :data="props[currentTab]" :key="timeline.state" :state="timeline.state" :tab="currentTab"
        app="retina" />

    <Modal :isOpen="openModal" @onClose="openModal = false">
        <div class="p-2 overflow-auto">
            <TablePalletReturn :dataRoute="palletRoute.index" :saveRoute="palletRoute.store"
                @onClose="()=>openModal = false" :descriptor="palletReturnDescriptor" />
        </div>
    </Modal>
</template>
