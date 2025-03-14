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
import TablePalletReturnPallets from "@/Components/Tables/Grp/Org/Fulfilment/TablePalletReturnPallets.vue"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from '@/types/PageHeading'
import palletReturnDescriptor from "@/Components/PalletReturn/Descriptor/PalletReturn"
import Tag from "@/Components/Tag.vue"
import { BoxStats, PDRNotes, PalletReturn, UploadPallet } from '@/types/Pallet'
import BoxStatsPalletReturn from '@/Pages/Grp/Org/Fulfilment/Return/BoxStatsPalletReturn.vue'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import ButtonWithLink from '@/Components/Elements/Buttons/ButtonWithLink.vue'
import { trans } from "laravel-vue-i18n"
import TableStoredItemReturnStoredItems from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemReturnStoredItems.vue"
import { get } from "lodash"
import PureInput from "@/Components/Pure/PureInput.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import Popover from "@/Components/Popover.vue"
import { Tabs as TSTabs } from "@/types/Tabs"
import { Action } from "@/types/Action"
import axios from "axios"
import TableFulfilmentTransactions from "@/Components/Tables/Grp/Org/Fulfilment/TableFulfilmentTransactions.vue";
import { notify } from "@kyvg/vue3-notification"
import PureMultiselectInfiniteScroll from '@/Components/Pure/PureMultiselectInfiniteScroll.vue'
import TableAttachments from "@/Components/Tables/Grp/Helpers/TableAttachments.vue";
import UploadAttachment from '@/Components/Upload/UploadAttachment.vue'
import { Table as TableTS } from '@/types/Table'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faIdCardAlt, faUser, faPaperclip, faBuilding, faEnvelope, faPhone, faMapMarkerAlt, faNarwhal, faUndo, faUndoAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import ModalConfirmationDelete from "@/Components/Utils/ModalConfirmationDelete.vue"
import { inject } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
library.add(faIdCardAlt, faUser, faPaperclip, faBuilding, faEnvelope, faPhone, faMapMarkerAlt, faNarwhal, faUndo, faUndoAlt )

const props = defineProps<{
    title: string
    tabs: TSTabs
    pallets?: {}
    stored_items?: {}
    services?: {}
    service_list_route: routeType
    physical_goods?: {}
    attachments?: TableTS
    attachmentRoutes: {
        attachRoute: routeType
        detachRoute: routeType
    }
    physical_good_list_route: routeType
    data: {
        data: PalletReturn
    }
    history?: {}
    pageHead: PageHeadingTypes
    updateRoute: routeType

    interest: {
        pallets_storage: boolean
        items_storage: boolean
        dropshipping: boolean
    }
    
    upload_spreadsheet: UploadPallet
    can_edit_transactions: boolean,
    box_stats: BoxStats
    notes_data: PDRNotes[]
    route_check_stored_items : routeType
    routeStorePallet : routeType

    option_attach_file?: {
		name: string
		code: string
	}[]
    stored_items_count?: number
}>()


const locale = inject('locale', aikuLocaleStructure)
const xstored_items_count = ref(props.stored_items_count || 0)

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const timeline = ref({ ...props.data?.data })
const openModal = ref(false)
const isLoadingButton = ref<string | boolean>(false)
const isLoadingData = ref<string | boolean>(false)
const isModalUploadOpen = ref(false)
const dataPGoodList = ref([])
const dataServiceList = ref([])

const formAddService = useForm({ service_id: '', quantity: 1, historic_asset_id: null })
const formAddPhysicalGood = useForm({ outer_id: '', quantity: 1, historic_asset_id: null })

const component = computed(() => {
    const components: Component = {
        pallets: TablePalletReturnPallets,
        stored_items: TableStoredItemReturnStoredItems,
        services: TableFulfilmentTransactions,
        physical_goods: TableFulfilmentTransactions,
        history: TableHistories,
        attachments: TableAttachments
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


// Tabs: Services
const onOpenModalAddService = async () => {
    isLoadingData.value = 'addService'
    try {
        const xxx = await axios.get(
            route(props.service_list_route.name, props.service_list_route.parameters)
        )
        dataServiceList.value = xxx?.data?.data || []
    } catch (error) {
        console.error(error)
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
                handleTabUpdate('services')
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
        route( data.route?.name, data.route?.parameters ),
        {
            preserveScroll: true,
            onSuccess: () => {
                closedPopover()
                formAddPhysicalGood.reset()
                isLoadingButton.value = false
                handleTabUpdate('physical_goods')
            },
            onError: (errors) => {
                isLoadingButton.value = false
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

const isModalUploadFileOpen = ref(false)

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- Button: Upload -->
        <template #button-upload="{ action }">
            <Button v-if="currentTab === 'pallets' || currentTab === 'stored_items'" @click="() => isModalUploadOpen = true"
                :style="action.style" :icon="action.icon" v-tooltip="action.tooltip"
            />
            <div v-else></div>
        </template>

        <!-- Button: delete Return -->
        <template #button-delete-return="{ action }">
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

        <!-- Button: Add Pallet -->
        <template #button-group-add-pallet="{ action }">
            <Button
                v-if="currentTab === 'pallets'"
                :style="action.style"
                :label="action.label"
                :icon="action.icon"
                :iconRight="action.iconRight"
                :key="`ActionButton${action.label}${action.style}`"
                :tooltip="action.tooltip"
                @click="() => (openModal = true)"
            />
            <div v-else />
        </template>

        <!-- Button: Add service (single) -->
        <template #button-group-add-service="{ action }" >
            <Popover>
                <template #button="{ open }">
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
                            <!-- <PureMultiselect
                                v-model="formAddService.service_id"
                                autofocus
                                caret
                                required
                                searchable
                                placeholder="Services"
                                :options="dataServiceList"
                                label="name"
                                valueProp="id"
                            /> -->

                            <PureMultiselectInfiniteScroll
                                v-model="formAddService.service_id"
                                :fetchRoute="props.service_list_route"
                                :placeholder="trans('Select Services')"
                                valueProp="id"
                            >
                                <template #singlelabel="{ value }">
                                    <div class="w-full text-left pl-4">{{ value.name }} <span class="text-sm text-gray-400">({{ locale.currencyFormat(value.currency_code, value.price) }}/{{ value.unit }})</span></div>
                                </template>

                                <template #option="{ option, isSelected, isPointed }">
                                    <div class="">{{ option.name }} <span class="text-sm text-gray-400">({{ locale.currencyFormat(option.currency_code, option.price) }}/{{ option.unit }})</span></div>
                                </template>
                            </PureMultiselectInfiniteScroll>
                            
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
                                :style="'save'"
                                :loading="isLoadingButton == 'addService'"
                                :label="'save'"
                                :disabled="!formAddService.service_id || !(formAddService.quantity > 0)"
                                full
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
        </template>


        <!-- Button: Add physical good (single) -->
        <template #button-group-add-physical-good="{ action }">
            <div class="relative ml-2" >
                <Popover>
                    <template #button="{ open }">
                        <Button
                            @click="open ? false : onOpenModalAddPGood()"
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
                                <!-- <PureMultiselect
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
                                        <div class="w-full text-left pl-4">{{ value.name }} <span class="text-gray-400">({{ value.code }})</span></div>
                                    </template>

                                    <template #option="{ option, isSelected, isPointed }">
                                        <div class="">{{ option.name }} <span :class="isSelected ? 'text-indigo-200' : 'text-gray-400'">({{ option.code }})</span></div>
                                    </template>
                                </PureMultiselect> -->

                                <PureMultiselectInfiniteScroll
                                    v-model="formAddPhysicalGood.outer_id"
                                    :fetchRoute="physical_good_list_route"
                                    :placeholder="trans('Select Physical Goods')"
                                    valueProp="id"
                                />

                                <p v-if="get(formAddPhysicalGood, ['errors', 'outer_id'])" class="mt-2 text-sm text-red-600">
                                    {{ formAddPhysicalGood.errors.outer_id }}
                                </p>
                            </div>
                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Quantity') }}: </span>
                                <PureInput
                                    v-model="formAddPhysicalGood.quantity"
                                    :placeholder="trans('Quantity')"
                                    @keydown.enter="() => onSubmitAddPhysicalGood(action, closed)"
                                />
                                <p v-if="get(formAddPhysicalGood, ['errors', 'quantity'])" class="mt-2 text-sm text-red-600">
                                    {{ formAddPhysicalGood.errors.quantity }}
                                </p>
                            </div>
                            <div class="flex justify-end mt-3">
                                <Button
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
       
        </template>

        <template #button-submit-stored-items="{ action }">
            <ButtonWithLink
                :routeTarget="action.route"
                :label="`${trans('Submit')} (${xstored_items_count})`"
                :icon="action.icon"
                :iconRight="action.iconRight"
                :style="action.style"
                :tooltip="xstored_items_count ? '' : action.tooltip"
                :disabled="
                    !xstored_items_count  // Need to improve
                "   
            />
        </template>

        <template #other>
            <Button
                v-if="currentTab === 'attachments'"
                @click="() => isModalUploadFileOpen = true"
                :label="trans('Attach file')"
                icon="fal fa-upload"
                type="secondary"
            />
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
    <BoxStatsPalletReturn :dataPalletReturn="data.data" :boxStats="box_stats" :updateRoute="updateRoute" />

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component 
        :is="component" 
        :data="props[currentTab]" 
        :state="timeline.state" 
        :key="timeline.state" 
        :tab="currentTab" 
        :can_edit_transactions="can_edit_transactions"
        :route_checkmark="currentTab == 'pallets' ? routeStorePallet : route_check_stored_items" 
        :palletReturn="data?.data"
        :detachRoute="attachmentRoutes?.detachRoute"
        @isStoredItemAdded="(e: boolean) => (console.log(e), e ? xstored_items_count++ : xstored_items_count--)"
    >
        <template #button-empty-state-attachments="{ action }">
            <Button
                v-if="currentTab === 'attachments'"
                @click="() => isModalUploadFileOpen = true"
                :label="trans('Attach file')"
                icon="fal fa-upload"
                type="secondary"
            />
        </template>
    </component>


    <UploadExcel
        v-if="upload_spreadsheet"
        v-model="isModalUploadOpen"
        scope="Pallet delivery"
        :title="{
            label: 'Upload your new pallet deliveries',
            information: `The list of column file: ${upload_spreadsheet.required_fields.join(', ')}`
        }"
        progressDescription="Adding Pallet Deliveries"        
        :upload_spreadsheet
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
</template>
