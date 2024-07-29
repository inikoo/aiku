<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from 'vue'
import type { Component } from 'vue'
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import TablePalletDeliveryPallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePalletDeliveryPallets.vue'
import Timeline from '@/Components/Utils/Timeline.vue'
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import { get } from 'lodash'
import { faExclamationTriangle } from '@fad'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import { PalletDelivery, BoxStats, PDRNotes, UploadPallet } from '@/types/Pallet'
import { Table as TableTS } from '@/types/Table'
import { Tabs as TSTabs } from '@/types/Tabs'
import '@vuepic/vue-datepicker/dist/main.css'
import BoxStatsPalletDelivery from '@/Pages/Grp/Org/Fulfilment/Delivery/BoxStatsPalletDelivery.vue'

import '@/Composables/Icon/PalletDeliveryStateEnum'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone, faConciergeBell, faCube, faCalendarDay, faPencil } from '@fal'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue";

import axios from 'axios'
import { Action } from '@/types/Action'
import TableFulfilmentTransactions from "@/Components/Tables/Grp/Org/Fulfilment/TableFulfilmentTransactions.vue";
import { notify } from '@kyvg/vue3-notification'

library.add(faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone,faExclamationTriangle, faConciergeBell, faCube, faCalendarDay, faPencil)



const props = defineProps<{
    title: string
    tabs: TSTabs
    pallets?: TableTS
    services?: TableTS
    physical_goods?: TableTS
    data?: {
        data: PalletDelivery
    }
    pageHead: PageHeadingTypes
    updateRoute: routeType

    interest: {
        pallets_storage: boolean
        items_storage: boolean
        dropshipping: boolean
    }

    // uploadRoutes: {
    //     upload: routeType
    //     download: routeType
    //     history: routeType
    // }

    upload_spreadsheet: UploadPallet

    locationRoute: routeType
    rentalRoute: routeType
    storedItemsRoute: {
        index: routeType
        store: routeType
    }
    box_stats: BoxStats
    notes_data: PDRNotes[]
    pallet_limits?: {
        status: string
        message: string
    }
    rental_lists?: [],
    
    service_lists?: [],
    service_list_route: routeType

    physical_good_lists?: []
    physical_good_list_route: routeType
}>()

console.log(props)


const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const isLoadingButton = ref<string | boolean>(false)
const isLoadingData = ref<string | boolean>(false)
const timeline = ref({ ...props.data?.data })

const formAddPallet = useForm({ notes: '', customer_reference: '', type : 'pallet' })
const formAddService = useForm({ service_id: '', quantity: 1, historic_asset_id: null })
const formAddPhysicalGood = useForm({ outer_id: '', quantity: 1, historic_asset_id: null })
const formMultiplePallet = useForm({ number_pallets: 1, type : 'pallet' })

const tableKey = ref(1)  // To re-render Table after click Confirm (so the Table retrieve the new props)
const typePallet = [
    { label : 'Pallet', value : 'pallet'},
    { label : 'Box', value : 'box'},
    { label : 'Oversize', value : 'oversize'}
]


const component = computed(() => {
    const components: Component = {
        pallets: TablePalletDeliveryPallets,
        services: TableFulfilmentTransactions,
        physical_goods: TableFulfilmentTransactions,
        history: TableHistories
    }
    return components[currentTab.value]

})


// Method: Add single pallet
const handleFormSubmitAddPallet = (data: {}, closedPopover: Function) => {
    isLoadingButton.value = true
    formAddPallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formAddPallet.reset()
            isLoadingButton.value = false
        },
        onError: (errors) => {
            isLoadingButton.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

// Method: Add many pallet
const handleFormSubmitAddMultiplePallet = (data: {}, closedPopover: Function) => {
    isLoadingButton.value = true
    formMultiplePallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formMultiplePallet.reset()
            isLoadingButton.value = false
        },
        onError: (errors) => {
            isLoadingButton.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

// Tabs: Services
const dataServiceList = ref([])
const onOpenModalAddService = async () => {
    isLoadingData.value = 'addService'
    try {
        const xxx = await axios.get(
            route(props.service_list_route.name, props.service_list_route.parameters)
        )
        dataServiceList.value = xxx?.data?.data || []
    } catch (error) {
        notify({
            title: 'Something went wrong.',
            text: 'Failed to fetch Services list',
            type: 'error',
        })
    }
    isLoadingData.value = false
}
const onSubmitAddService = (data: Action, closedPopover: Function) => {
    const selectedHistoricAssetId = dataServiceList.value.filter(service => service.id == formAddService.service_id)[0].historic_asset_id
    
    formAddService.historic_asset_id = selectedHistoricAssetId
    isLoadingButton.value = 'addService'

    formAddService.post(
        route(data.route?.name, {...data.route?.parameters }),
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                formAddService.reset()
            },
            onError: (errors) => {
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to add service, please try again.',
                    type: 'error',
                })
            },
            onFinish: () => {
                isLoadingButton.value = false
            }
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
            text: 'Failed to fetch Physical Goods list',
            type: 'error',
        })
    }
    isLoadingData.value = false
}
const onSubmitAddPhysicalGood = (data: Action, closedPopover: Function) => {
    const selectedHistoricAssetId = dataPGoodList.value.filter(pgood => pgood.id == formAddPhysicalGood.outer_id)[0].historic_asset_id
    formAddPhysicalGood.historic_asset_id = selectedHistoricAssetId

    isLoadingButton.value = 'addPGood'
    formAddPhysicalGood.post(
        route(data.route?.name, data.route?.parameters),
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                formAddPhysicalGood.reset()
            },
            onError: (errors) => {
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to add physical good, please try again.',
                    type: 'error',
                })
            },
            onFinish: () => {
                isLoadingButton.value = false
            }
        }
    )
}


const changeTableKey = () => {
    tableKey.value = tableKey.value + 1
}

// Section: Upload spreadsheet
const isModalUploadOpen = ref(false)

const changePalletType=(form,fieldName,value)=>{
    form[fieldName] = value
}

watch(() => props.data, (newValue) => {
    timeline.value = newValue.data
}, { deep: true })


// console.log(currentTab.value)

</script>

<template>
    <!-- <pre>{{ data.data }}</pre> -->
<!-- {{ props.service_list_route.name }} -->
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- Button: Upload -->
        <template #button-group-upload="{ action }">
            <Button v-if="currentTab === 'pallets'" @click="() => isModalUploadOpen = true"
                :style="action.style" :icon="action.icon" v-tooltip="action.tooltip"
                class="rounded-l-md rounded-r-none border-none" />
            <div v-else></div>
        </template>

        <!-- Button: Add multiple pallet -->
        <template #button-group-multiple="{ action }">
            <Popover  class="relative h-full">
                <template #button>
                    <Button v-if="currentTab === 'pallets'" :style="action.style" :icon="action.icon"
                        :iconRight="action.iconRight"
                        :key="`ActionButton${action.label}${action.style}`"
                        :tooltip="trans('Add multiple pallets')"
                        class="rounded-none border-none" />
                    <div v-else></div>
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <span class="text-xs  my-2">{{ trans('Type') }}: </span>
                        <div class="flex items-center gap-x-2">
                            <div v-for="(typeData, typeIdx) in typePallet" :key="typeIdx"
                                class="relative py-2 px-1 flex items-center">
                                <input type="checkbox" :id="typeData.value" :value="typeData.value"
                                    :checked="formMultiplePallet.type == typeData.value"
                                    @input="changePalletType(formMultiplePallet,'type',typeData.value)"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 cursor-pointer">
                                <label :for="typeData.value" class="pl-2 cursor-pointer select-none">{{ typeData.label }}</label>
                            </div>
                        </div>
                        <span class="text-xs  my-2">Number of pallets: </span>
                        <div>
                            <PureInput v-model="formMultiplePallet.number_pallets" autofocus placeholder="1-100"
                                type="number" :minValue="1" :maxValue="100"
                                @update:modelValue="() => formMultiplePallet.errors.number_pallets = ''"
                                @keydown.enter="() => formMultiplePallet.number_pallets ? handleFormSubmitAddMultiplePallet(action, closed) : ''" />
                            <p v-if="get(formMultiplePallet, ['errors', 'number_pallets'])"
                                class="mt-2 text-xxs italic text-red-600">
                                {{ formMultiplePallet.errors.number_pallets }}
                            </p>
                        </div>

                        <div class="flex justify-end mt-3">
                            <Button
                                :style="'create'"
                                :loading="!!isLoadingButton"
                                :disabled="formMultiplePallet.number_pallets < 1"
                                :key="formMultiplePallet.number_pallets"
                                full
                                label='add'
                                @click="() => handleFormSubmitAddMultiplePallet(action, closed)"
                            />
                        </div>
                    </div>
                </template>
            </Popover>
        </template>

        <!-- Button: Add pallet (single) -->
        <template #button-group-add-pallet="{ action }">
            <div class="relative" v-if="currentTab === 'pallets'">
                <Popover>
                    <template #button>
                        <Button
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
                            class="rounded-l-none rounded-r-md border-none"
                        />
                    </template>
                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <span class="text-xs px-1 my-2">{{ trans('Type') }}: </span>
                            <div class="flex items-center">
                                <div v-for="(typeData, typeIdx) in typePallet" :key="typeIdx"
                                    class="relative py-3 mr-4 flex items-center">
                                    <input type="checkbox" :id="typeData.value" :value="typeData.value"
                                        :checked="formAddPallet.type == typeData.value"
                                        @input="changePalletType(formAddPallet,'type',typeData.value)"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4 cursor-pointer">
                                    <label :for="typeData.value" class="ml-2 cursor-pointer">
                                        {{ typeData.label }}
                                    </label>
                                </div>
                            </div>
                            <span class="text-xs px-1 my-2">{{ trans('Reference') }}: </span>
                            <div>
                                <PureInput v-model="formAddPallet.customer_reference" autofocus placeholder="Reference"
                                    @keydown.enter="() => handleFormSubmitAddPallet(action, closed)" />
                                <p v-if="get(formAddPallet, ['errors', 'customer_reference'])"
                                    class="mt-2 text-sm text-red-600">
                                    {{ formAddPallet.errors.customer_reference }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Notes') }}: </span>
                                <textarea
                                    class="placeholder:text-gray-400 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
                                    v-model="formAddPallet.notes" placeholder="Notes">
                                </textarea>
                                <p v-if="get(formAddPallet, ['errors', 'notes'])" class="mt-2 text-sm text-red-600">
                                    {{ formAddPallet.errors.notes }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button
                                    :style="'create'"
                                    :loading="isLoadingButton"
                                    full
                                    :label="'add'"
                                    @click="() => handleFormSubmitAddPallet(action, closed)"
                                />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
            <div v-else></div>
        </template>

        <!-- Button: Add service -->
        <template #button-group-add-service="{ action }">
            <div class="relative" v-if="currentTab === 'services'">
                <Popover>
                    <template #button="{open}">
                        <Button
                            @click="() => open ? false : onOpenModalAddService()"
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
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
                                    searchable
                                    placeholder="Select service"
                                    :options="dataServiceList"
                                    label="name"
                                    valueProp="id"
                                >
                                    <template #label="{ value }">
                                        <div class="w-full text-left pl-4">{{ value.name }} <span class="text-sm text-gray-400">({{ value.code }})</span></div>
                                    </template>

                                    <template #option="{ option, isSelected, isPointed }">
                                        <div class="">{{ option.name }} <span class="text-sm" :class="isSelected ? 'text-indigo-200' : 'text-gray-400'">({{ option.code }})</span></div>
                                    </template>
                                </PureMultiselect>
                                <p v-if="get(formAddService, ['errors', 'service_id'])" class="mt-2 text-sm text-red-500">
                                    {{ formAddService.errors.service_id }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Quantity') }}: </span>
                                <PureInput
                                    v-model="formAddService.quantity"
                                    :placeholder="trans('Quantity')"
                                    @keydown.enter="() => onSubmitAddService(action, closed)"
                                />
                                <p v-if="get(formAddService, ['errors', 'quantity'])" class="mt-2 text-sm text-red-600">
                                    {{ formAddService.errors.quantity }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button
                                    @click="() => onSubmitAddService(action, closed)"
                                    :style="'save'"
                                    :loading="isLoadingButton == 'addService'"
                                    :disabled="!formAddService.service_id || !(formAddService.quantity > 0)"
                                    label="Save"
                                    full
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
            <div v-else></div>
        </template>

        <!-- Button: Add physical good -->
        <template #button-group-add-physical-good="{ action }">
            <div class="relative" v-if="currentTab === 'physical_goods'">
                <Popover>
                    <template #button="{ open }">
                        <Button
                            @click="open ? false : onOpenModalAddPGood()"
                            :key="`ActionButton${action.label}${action.style}`"
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
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
                                    searchable
                                    placeholder="Physical Goods"
                                    :options="dataPGoodList"
                                    label="name"
                                    valueProp="id"
                                >
                                    <template #label="{ value }">
                                        <div class="w-full text-left pl-4">{{ value.name }} <span class="text-sm text-gray-400">({{ value.code }})</span></div>
                                    </template>

                                    <template #option="{ option, isSelected, isPointed }">
                                        <div class="">{{ option.name }} <span class="text-sm" :class="isSelected ? 'text-indigo-200' : 'text-gray-400'">({{ option.code }})</span></div>
                                    </template>
                                </PureMultiselect>
                                <p v-if="get(formAddPhysicalGood, ['errors', 'outer_id'])" class="mt-2 text-sm text-red-600">
                                    {{ formAddPhysicalGood.errors.outer_id }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Quantity') }}: </span>
                                <PureInput
                                    v-model="formAddPhysicalGood.quantity"
                                    placeholder="Quantity"
                                    @keydown.enter="() => onSubmitAddPhysicalGood(action, closed)"
                                />
                                <p v-if="get(formAddPhysicalGood, ['errors', 'quantity'])"
                                    class="mt-2 text-sm text-red-600">
                                    {{ formAddPhysicalGood.errors.quantity }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button
                                    :key="'button' + formAddPhysicalGood.outer_id + formAddPhysicalGood.quantity"
                                    :style="'save'"
                                    :loading="isLoadingButton == 'addPGood'"
                                    :disabled="!formAddPhysicalGood.outer_id || !(formAddPhysicalGood.quantity > 0)"
                                    :label="'save'"
                                    full
                                    @click="() => onSubmitAddPhysicalGood(action, closed)"
                                />
                            </div>

                            <!-- Loading: fetching pgood list -->
                            <div v-if="isLoadingData === 'addPGood'" class="bg-white/50 absolute inset-0 flex place-content-center items-center">
                                <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin text-5xl' fixed-width aria-hidden='true' />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
            <div v-else></div>
        </template>
    </PageHeading>

    <!-- Section: Pallet Warning -->
    <div v-if="pallet_limits?.status">
        <div class="p-4"
            :class="{
                'bg-yellow-50': pallet_limits?.status === 'almost',
                'bg-orange-200': pallet_limits?.status === 'limit',
                'bg-red-200': pallet_limits?.status === 'exceeded',
            }"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <font-awesome-icon :icon="['fad', 'exclamation-triangle']" class="h-5 w-5 text-amber-500"
                        aria-hidden="true"
                        :class="{
                            'text-yellow-50': pallet_limits?.status === 'almost',
                            'text-orange-200': pallet_limits?.status === 'limit',
                            'text-red-600': pallet_limits?.status === 'exceeded',
                        }"
            />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">{{ trans('Attention needed') }}</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ pallet_limits?.message }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Section: Box Note -->
    <div class="grid grid-cols-3 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
        <BoxNote v-for="(note, index) in notes_data" :key="index+note.label" :noteData="note"
            :updateRoute="updateRoute" />
    </div>

    <!-- Section: Timeline -->
    <div v-if="timeline.state != 'in-process'" class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
        <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="6" />
    </div>

    <!-- Box -->
    <BoxStatsPalletDelivery :dataPalletDelivery="data.data" :boxStats="box_stats" :updateRoute />

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />

    <div class="pb-12">
        <component
            :is="component"
            :key="timeline.state"
            :data="props[currentTab as keyof typeof props]"
            :state="timeline.state"
            :tab="currentTab"
            :tableKey="tableKey"
            @renderTableKey="changeTableKey"
            :locationRoute="locationRoute"
            :storedItemsRoute="storedItemsRoute"
            :rentalRoute="rentalRoute"
            :rentalList="props.rental_lists"
        />
    </div>

    <UploadExcel
        v-model="isModalUploadOpen"
        scope="Pallet delivery"
        :title="{
            label: 'Upload your new pallet deliveries',
            information: 'The list of column file: customer_reference, notes, stored_items'
        }"
        progressDescription="Adding Pallet Deliveries"        
        :upload_spreadsheet
        :additionalDataToSend="interest.pallets_storage ? ['stored_items'] : undefined"
    />

    <!--     <pre>{{ props.services.data?.[0]?.reference }}</pre>
    <pre>{{ $inertia.page.props.queryBuilderProps.services.columns }}</pre>-->
</template>
