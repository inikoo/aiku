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
import { computed, inject, ref } from 'vue'
import type { Component } from 'vue'
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import TablePalletDeliveryPallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePalletDeliveryPallets.vue'
import Timeline from '@/Components/Utils/Timeline.vue'
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import { get } from 'lodash-es'
import { faExclamationTriangle } from '@fad'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import { ctrans } from "@/Composables/useTrans"
import { routeType } from '@/types/route'
import { PageHeadingTypes } from  '@/types/PageHeading'
import { PalletDelivery, BoxStats, PDRNotes, UploadPallet } from '@/types/Pallet'
import { Table as TableTS } from '@/types/Table'
import { Tabs as TSTabs } from '@/types/Tabs'
import '@vuepic/vue-datepicker/dist/main.css'
import BoxStatsPalletDelivery from '@/Pages/Grp/Org/Fulfilment/Delivery/BoxStatsPalletDelivery.vue'
import { Menu, MenuButton, MenuItems } from '@headlessui/vue'
import '@/Composables/Icon/PalletDeliveryStateEnum'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faPaperclip, faEnvelope, faPhone, faConciergeBell, faCube, faCalendarDay, faPencil, faUndoAlt, faTrashAlt, faFileExcel } from '@fal'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import { Action } from '@/types/Action'
import TableFulfilmentTransactions from "@/Components/Tables/Grp/Org/Fulfilment/TableFulfilmentTransactions.vue";
import ModalBulkAddTransactions from '@/Components/Fulfilment/ModalBulkAddTransactions.vue'
import ModalConfirmationDelete from '@/Components/Utils/ModalConfirmationDelete.vue'
import TableAttachments from "@/Components/Tables/Grp/Helpers/TableAttachments.vue";
import UploadAttachment from '@/Components/Upload/UploadAttachment.vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import HelpArticles from '@/Components/Utils/HelpArticles.vue'
import ModalAfterConfirmationDelete from '@/Components/Utils/ModalAfterConfirmationDelete.vue'
import ModalSupervisorList from '@/Components/Utils/ModalSupervisorList.vue'

library.add(faUser, faFileExcel ,faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faPaperclip, faEnvelope, faPhone,faExclamationTriangle, faConciergeBell, faCube, faCalendarDay, faPencil, faUndoAlt, faTrashAlt)

interface UploadSection {
    title: {
        label: string
        information: string
    }
    progressDescription: string
    upload_spreadsheet: UploadPallet
    preview_template: {
        header: string[]
        rows: {}[]
    }
}

const props = defineProps<{
    title: string
    tabs: TSTabs
    pallets?: TableTS
    services?: TableTS
    physical_goods?: TableTS
    attachments?: TableTS
    attachmentRoutes: {
        attachRoute: routeType
        detachRoute: routeType
    }
    can_edit_transactions?: boolean,
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

    option_attach_file?: {
		name: string
		code: string
	}[]
    export_excel_route: {}
    upload_pallet: UploadSection
    upload_stored_item: UploadSection

    help_articles: {
        label: string
        description: string
        url: string
        type?: 'video' | 'article'
    }[]
}>()

const locale = inject('locale', aikuLocaleStructure)

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const isLoadingButton = ref<string | boolean>(false)

const formAddPallet = useForm({ notes: '', customer_reference: '', type : 'pallet' })
const formMultiplePallet = useForm({ number_pallets: 1, type : 'pallet' })

const tableKey = ref(1)  // To re-render Table after click Confirm (so the Table retrieve the new props)
const typePallet = [
    { label : 'Carton', value : 'box'},
    { label : 'Pallet', value : 'pallet'},
    { label : 'Oversize', value : 'oversize'}
]


const component = computed(() => {
    const components: Component = {
        pallets: TablePalletDeliveryPallets,
        services: TableFulfilmentTransactions,
        physical_goods: TableFulfilmentTransactions,
        history: TableHistories,
        attachments: TableAttachments
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
            handleTabUpdate('pallets')
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


// Tabs: Services and Physical Goods
const bulkAddRoute = ref<routeType | null>(null)
const bulkAddTab = ref<'services' | 'physical_goods'>('services')
const isModalBulkAddOpen = ref(false)
const onOpenBulkAdd = (action: { route: routeType }, tab: 'services' | 'physical_goods') => {
    bulkAddRoute.value = action.route
    bulkAddTab.value = tab
    isModalBulkAddOpen.value = true
}


const changeTableKey = () => {
    tableKey.value = tableKey.value + 1
}

// Section: Upload spreadsheet
const isModalUploadPallet = ref(false)
const isModalUploadStoredItemOpen = ref(false)

const changePalletType=(form,fieldName,value)=>{
    form[fieldName] = value
}

const isModalUploadFileOpen = ref(false)

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        
        <!-- Button: Upload -->
        <template #button-group-upload="{ action }">
            <Menu v-slot="{ close }" as="div" class="relative inline-block text-left">
                <div>
                    <MenuButton class="">
                        <Button
                            v-if="currentTab === 'pallets'"
                            :label="action.label"
                            :style="action.style"
                            :icon="action.icon"
                            v-tooltip="action.tooltip"
                            class="rounded-l-ms rounded-r-none border-r-0"
                        />
                        <div v-else></div>
                    </MenuButton>
                </div>

                <transition name="headlessui2">
                    <MenuItems class="z-10 absolute right-0 p-1 mt-2 w-fit origin-top-right rounded-md bg-white shadow-lg ring-1 ring-indigo-500/50 focus:outline-none" >
                        <div @click="() => (isModalUploadPallet = true, close())" class="whitespace-nowrap px-3 py-1 rounded hover:bg-gray-200 cursor-pointer">
                            <FontAwesomeIcon icon='fal fa-upload' class='' fixed-width aria-hidden='true' />
                            {{ ctrans("Upload pallet") }}
                        </div>
                        <div @click="() => (isModalUploadStoredItemOpen = true, close())" class="whitespace-nowrap px-3 py-1 rounded hover:bg-gray-200 cursor-pointer">
                            <FontAwesomeIcon icon='fal fa-upload' class='' fixed-width aria-hidden='true' />
                            {{ ctrans("Upload Customer's SKO") }}
                        </div>
                    </MenuItems>
                </transition>
            </Menu>
        </template>
        
        <template #button-delete-delivery="{ action }">
            <div>
                <ModalConfirmationDelete
                    :routeDelete="action.route"
                    isFullLoading
                    isWithMessage
                >
                    <template #default="{ isOpenModal, changeModel }">

                        <Button
                            @click="() => changeModel()"
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :iconRight="action.iconRight"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
                        />

                    </template>
                </ModalConfirmationDelete>
            </div>
        </template>
        
        <!-- Button: delete Delivery -->
        <template #button-delete-booked-in="{ action }">
            <div>
                <template v-if="action.supervisor">
                <ModalAfterConfirmationDelete
                    :routeDelete="action.route"
                    :data="data?.data"
                    isFullLoading
                    isWithMessage
                >
                    <template #default="{ isOpenModal, changeModel }">

                        <Button
                            @click="() => changeModel()"
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :iconRight="action.iconRight"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
                        />

                    </template>
                </ModalAfterConfirmationDelete>
                </template>
                <template v-else>
                    <ModalSupervisorList
                    :routeDelete="action.route"
                    :routeSupervisor="action.supervisors_route"
                    isFullLoading
                    isWithMessage
                >
                    <template #default="{ isOpenModal, changeModel }">

                        <Button
                            @click="() => changeModel()"
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :iconRight="action.iconRight"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
                        />

                    </template>
                    </ModalSupervisorList>
                </template>
            </div>
        </template>

        <!-- Button: Add multiple pallet -->
        <template #button-group-multiple="{ action }">
            <Popover  class="relative h-full">
                <template #button>
                    <Button v-if="currentTab === 'pallets'" :style="action.style" :icon="action.icon"
                        :iconRight="action.iconRight"
                        :key="`ActionButton${action.label}${action.style}`"
                        :tooltip="ctrans('Add multiple pallets')"
                        class="rounded-l-sm rounded-r-md border-l-0" />
                    <div v-else></div>
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <span class="text-xs  my-2">{{ ctrans('Type') }}: </span>
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
            <div class="relative">
                <Popover>
                    <template #button>
                        <Button
                            :style="action.style"
                            :label="action.label"
                            :icon="action.icon"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip"
                            class=" rounded-r-md ml-2"
                        />
                    </template>
                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <span class="text-xs px-1 my-2">{{ ctrans('Type') }}: </span>
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
                            <span class="text-xs px-1 my-2">{{ ctrans('Reference') }}: </span>
                            <div>
                                <PureInput v-model="formAddPallet.customer_reference" autofocus placeholder="Reference"
                                    @keydown.enter="() => handleFormSubmitAddPallet(action, closed)" />
                                <p v-if="get(formAddPallet, ['errors', 'customer_reference'])"
                                    class="mt-2 text-sm text-red-600">
                                    {{ formAddPallet.errors.customer_reference }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ ctrans('Notes') }}: </span>
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
        </template>

        <!-- Button: Add services -->
        <template #button-group-add-service="{ action }">
            <Button
                :style="action.style"
                :label="action.label"
                :icon="action.icon"
                :key="`ActionButton${action.label}${action.style}`"
                :tooltip="action.tooltip"
                class="rounded-r-md ml-2"
                @click="() => onOpenBulkAdd(action, 'services')"
            />
        </template>

        <!-- Button: Add physical goods -->
        <template #button-group-add-physical-good="{ action }">
            <Button
                :key="`ActionButton${action.label}${action.style}`"
                :style="action.style"
                :label="action.label"
                :icon="action.icon"
                :tooltip="action.tooltip"
                class="rounded-r-md ml-2"
                @click="() => onOpenBulkAdd(action, 'physical_goods')"
            />
        </template>

        <template #other>
            <a v-if="export_excel_route" :href="export_excel_route?.name ? route(export_excel_route?.name, export_excel_route.parameters) : '#'"
                    as="a" target="_blank" class="flex items-center">
                <Button
                    v-if="export_excel_route"
                    :href="'#'"
                    :label="`${ctrans('Excel')}`"
                    icon="fal fa-file-excel"
                    type="secondary"
                    target="_blank"
                    class="text-gray-600 hover:text-green-600 transition-colors duration-200"
                />
            </a>
        </template>
    </PageHeading> 

    <ModalBulkAddTransactions
        v-if="bulkAddRoute"
        v-model="isModalBulkAddOpen"
        :title="bulkAddTab === 'services' ? ctrans('Add services') : ctrans('Add physical goods')"
        :fetchRoute="bulkAddTab === 'services' ? service_list_route : physical_good_list_route"
        :submitRoute="bulkAddRoute"
        :isService="bulkAddTab === 'services'"
        @success="handleTabUpdate(bulkAddTab)"
    />

    <!-- Section: Pallet Warning -->
    <div v-if="pallet_limits?.status">
        <div class="p-4"
            :class="{
                'bg-yellow-50': pallet_limits?.status === 'almost',
                'bg-orange-200': pallet_limits?.status === 'limit',
                'bg-red-200': pallet_limits?.status === 'exceeded',
            }"
        >
            <div class="flex" :class="{
                'text-amber-500': pallet_limits?.status === 'almost',
                'text-orange-600': pallet_limits?.status === 'limit',
                'text-red-600': pallet_limits?.status === 'exceeded',
            }">
                <div class="flex-shrink-0">
                    <font-awesome-icon :icon="['fad', 'exclamation-triangle']" class="text-lg"
                        fixed-width aria-hidden="true"
                        
                    />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium">{{ ctrans('Attention needed') }}</h3>
                    <div class="text-xs opacity-70 ">
                        <p>{{ pallet_limits?.message }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Section: Box Note -->
    <div class="grid grid-cols-3 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
        <BoxNote
            v-for="(note, index) in notes_data"
            :key="index+note.label"
            :noteData="note"
            :updateRoute="updateRoute"
        />
    </div>

    <!-- Section: Timeline -->
    <div v-if="props.data?.data?.state != 'in_process'" class="mt-4 sm:mt-0 border-b border-gray-200 pb-2">
        <Timeline :options="props.data?.data?.timeline" :state="props.data?.data?.state" :slidesPerView="6" />
    </div>

    <!-- Box -->
    <BoxStatsPalletDelivery :dataPalletDelivery="data.data" :boxStats="box_stats" :updateRoute />

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />

    <div class="pb-12">
        <component
            :is="component"
            :key="props.data?.data?.state"
            :data="props[currentTab as keyof typeof props]"
            :state="props.data?.data?.state"
            :tab="currentTab"
            :tableKey="tableKey"
            @xxrenderTableKey="changeTableKey"
            :locationRoute="locationRoute"
            :storedItemsRoute="storedItemsRoute"
            :rentalRoute="rentalRoute"
            :rentalList="props.rental_lists"
            :detachRoute="attachmentRoutes?.detachRoute"
            :can_edit_transactions="can_edit_transactions"
        >
            <template #button-empty-state-attachments="{ action }">
                <Button
                    v-if="currentTab === 'attachments'"
                    @click="() => isModalUploadFileOpen = true"
                    :label="ctrans('Attach file')"
                    icon="fal fa-upload"
                    type="secondary"
                />
            </template>
        </component>
    </div>

    <UploadExcel
         v-if="upload_pallet"
        v-model="isModalUploadPallet"
        :title="upload_pallet.title"
        :progressDescription="upload_pallet.progressDescription"
        :upload_spreadsheet="upload_pallet.upload_spreadsheet"
        :preview_template="upload_pallet.preview_template"
        :additionalDataToSend="interest.pallets_storage ? ['stored_items'] : undefined"
    />

    <UploadExcel
        v-if="upload_stored_item"
        v-model="isModalUploadStoredItemOpen"
        :title="upload_stored_item.title"
        :progressDescription="upload_stored_item.progressDescription"
        :preview_template="upload_stored_item.preview_template"
        :upload_spreadsheet="upload_stored_item.upload_spreadsheet"
        :additionalDataToSend="interest.pallets_storage ? ['stored_items'] : undefined"
    />

    <UploadAttachment
        v-model="isModalUploadFileOpen"
        scope="attachment"
        :title="{
            label: 'Upload your file',
            information: 'The list of column file: customer_reference, notes, stored_items'
        }"
        progressDescription="Adding Pallet Deliveries"
        :attachmentRoutes
        :options="props.option_attach_file"
    />

    <HelpArticles
        :articles="help_articles"
    />

</template>
