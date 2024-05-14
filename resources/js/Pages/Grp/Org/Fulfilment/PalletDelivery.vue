<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch, onMounted } from 'vue'
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/TableHistories.vue"
import TablePalletDeliveryPallets from '@/Components/Tables/TablePalletDeliveryPallets.vue'
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
import BoxStatsPalletDelivery from "@/Components/Pallet/BoxStatsPalletDelivery.vue"
import JsBarcode from 'jsbarcode'
import Checkbox from '@/Components/Forms/Fields/Checkbox.vue'

import { PalletDelivery, BoxStats, PDRNotes } from '@/types/Pallet'
import { Table } from '@/types/Table'
import { Tabs as TSTabs } from '@/types/Tabs'

import '@/Composables/Icon/PalletDeliveryStateEnum'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone } from '@fal'
import { useFormatTime } from '@/Composables/useFormatTime'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(faUser, faTruckCouch, faPallet, faPlus, faFilePdf, faIdCardAlt, faEnvelope, faPhone,faExclamationTriangle)

const props = defineProps<{
    title: string
    tabs: TSTabs
    pallets?: Table
    data?: {
        data: PalletDelivery
    }
    history?: {}
    pageHead: PageHeadingTypes
    updateRoute: routeType
    uploadRoutes: {
        download: routeType
        history: routeType
    }
    locationRoute : routeType
    rentalRoute : routeType
    storedItemsRoute : {
        index: routeType
        store: routeType
    }
    box_stats: BoxStats
    notes_data: PDRNotes[]
    pallet_limits : {
        status : String,
        message : String
    }
}>()

console.log('ewqewq', props.box_stats)

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const loading = ref(false)
const timeline = ref({ ...props.data.data })
const dataModal = ref({ isModalOpen: false })
const formAddPallet = useForm({ notes: '', customer_reference: '', type : 'pallet' })
const formMultiplePallet = useForm({ number_pallets: 1, type : 'pallet' })

// Method: Add single pallete
const handleFormSubmitAddPallet = (data: {}, closedPopover: Function) => {
    loading.value = true
    formAddPallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formAddPallet.reset('notes', 'customer_reference','type')
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

// Method: Add many pallete
const handleFormSubmitAddMultiplePallet = (data: {}, closedPopover: Function) => {
    loading.value = true
    formMultiplePallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formMultiplePallet.reset('number_pallets','type')
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

/* const updateState = async ({ step, options }) => {

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
} */


// Button: Confirm
const handleClickConfirm = async (action: { method: any, name: string, parameters: { palletDelivery: number } }) => {
    loading.value = true
    router.post(route(action.name, action.parameters), {}, {
        onError: (e) => {
            console.warn('Error on confirm', e)
        },
        onSuccess: (e) => {
            // console.log('on success', e)
            changeTableKey()
        },
        onFinish: (e) => {
            // console.log('11111', e)
            loading.value = false
        }
    })
}

const tableKey = ref(1)  // To re-render Table after click Confirm (so the Table retrieve the new props)
const changeTableKey = () => {
    tableKey.value = tableKey.value + 1
}

const component = computed(() => {
    const components: {[key: string]: string} = {
        pallets: TablePalletDeliveryPallets,
        history: TableHistories
    }
    return components[currentTab.value]

})

// Method: open modal Upload
const onUploadOpen = (action) => {
    dataModal.value.isModalOpen = true
    dataModal.value.uploadRoutes = action.route
}

watch(() => props.data, (newValue) => {
    timeline.value = newValue.data
}, { deep: true })

onMounted(() => {
    JsBarcode('#palletDeliveryBarcode', 'pad-' + route().v().params.palletDelivery, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: '50%',
        displayValue: false
    });
})


const changePalletType=(form,fieldName,value)=>{
    form[fieldName] = value
}



const typePallet = [
    { label : 'Pallet', value : 'pallet'}, 
    { label : 'Box', value : 'box'}, 
    { label : 'Oversize', value : 'oversize'}
]


</script>

<template>
    <!-- <pre>{{ data.data }}</pre> -->

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- Button: Upload -->
        <template #button-group-upload="{ action }">
            <Button @click="() => onUploadOpen(action.button)" :style="action.button.style" :icon="action.button.icon"
                v-tooltip="action.button.tooltip" class="rounded-l rounded-r-none border-none" />
        </template>

        <!-- Button: Add many pallete -->
        <template #button-group-multiple="{ action }">
            <Popover width="w-full" class="relative h-full">
                <template #button>
                    <Button :style="action.button.style" :icon="action.button.icon" :iconRight="action.button.iconRight"
                        :key="`ActionButton${action.button.label}${action.button.style}`"
                        :tooltip="'Add multiple pallet'" class="rounded-none border-none" />
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <span class="text-xs  my-2">{{ trans('Type') }}: </span>
                        <div class="flex items-center">
                            <div v-for="(typeData, typeIdx) in typePallet" :key="typeIdx" class="relative py-3 mr-4">
                                <div>
                                    <input type="checkbox" :id="typeData.value" :value="typeData.value"
                                        :checked="formMultiplePallet.type == typeData.value"
                                        @input="changePalletType(formMultiplePallet,'type',typeData.value)"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    <label :for="typeData.value" class="ml-2">{{ typeData.label }}</label>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs  my-2">Number of pallets: </span>
                        <div>
                            <PureInput v-model="formMultiplePallet.number_pallets" autofocus placeholder="1-100"
                                type="number" :minValue="1" :maxValue="100"
                                @update:modelValue="() => formMultiplePallet.errors.number_pallets = ''"
                                @keydown.enter="() => formMultiplePallet.number_pallets ? handleFormSubmitAddMultiplePallet(action.button, closed) : ''" />
                            <p v-if="get(formMultiplePallet, ['errors', 'number_pallets'])"
                                class="mt-2 text-xxs italic text-red-600">
                                {{ formMultiplePallet.errors.number_pallets }}
                            </p>
                        </div>

                        <div class="flex justify-end mt-3">
                            <Button :style="'save'" :loading="loading" :disabled="!formMultiplePallet.number_pallets"
                                :key="formMultiplePallet.number_pallets"
                                @click="() => handleFormSubmitAddMultiplePallet(action.button, closed)" />
                        </div>
                    </div>
                </template>
            </Popover>
        </template>

        <!-- Button: Add pallet (single) -->
        <template #button-group-add-pallet="{ action: action }">
            <div class="relative">
                <Popover width="w-full">
                    <template #button>
                        <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
                            :key="`ActionButton${action.button.label}${action.button.style}`"
                            :tooltip="action.button.tooltip" class="rounded-l-none rounded-r border-none " />
                    </template>

                    <template #content="{ close: closed }">
                        <div class="w-[350px]">

                            <span class="text-xs px-1 my-2">{{ trans('Type') }}: </span>

                            <div class="flex items-center">
                                <div v-for="(typeData, typeIdx) in typePallet" :key="typeIdx"
                                    class="relative py-3 mr-4">
                                    <div>
                                        <input type="checkbox" :id="typeData.value" :value="typeData.value"
                                            :checked="formAddPallet.type == typeData.value"
                                            @input="changePalletType(formAddPallet,'type',typeData.value)"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <label :for="typeData.value" class="ml-2">{{ typeData.label }}</label>
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs px-1 my-2">{{ trans('Reference') }}: </span>
                            <div>
                                <PureInput v-model="formAddPallet.customer_reference" autofocus placeholder="Reference"
                                    @keydown.enter="() => handleFormSubmitAddPallet(action.button, closed)" />
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
                                <Button :style="'save'" :loading="loading" :label="'save'"
                                    @click="() => handleFormSubmitAddPallet(action.button, closed)" />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
        </template>

        <!-- Button: Confirm -->
        <template #button-confirm="{ action: action }">
            <div>
                <!-- <Link as="Button" :style="action.action.style"
                    :label="action.action.label"
                    :loading="loading" :href="route(action.action.route.name, action.action.route.parameters)" method="post">
                    <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
                </Link> -->
                <Button @click="handleClickConfirm(action.action.route)" :style="action.action.style"
                    :label="action.action.label" :loading="loading" />
            </div>
        </template>
    </PageHeading>

    <!-- Section: Warning -->
    <div v-if="pallet_limits && pallet_limits.status == 'exceeded'">
        <div class="rounded-md bg-yellow-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <font-awesome-icon :icon="['fad', 'exclamation-triangle']" class="h-5 w-5 text-yellow-400"
                        aria-hidden="true" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Attention needed</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ pallet_limits.message }}</p>
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
    <div v-if="timeline.state != 'in-process'" class="border-b border-gray-200 pb-2">
        <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="6" />
    </div>

    <!-- Box -->
    <div class="h-min grid grid-cols-4 border-b border-gray-200 divide-x divide-gray-300">
        <!-- Box: Customer -->
        <BoxStatsPalletDelivery class=" pb-2 py-5 px-3" tooltip="Customer" :label="data?.data.customer_name"
            icon="fal fa-user">
            <!-- Field: Reference -->
            <Link as="a" v-if="box_stats.fulfilment_customer.customer.reference"
                :href="route('grp.org.fulfilments.show.crm.customers.show', [route().params.organisation, box_stats.fulfilment_customer.fulfilment.slug, box_stats.fulfilment_customer.slug])"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer secondaryLink">
            <dt v-tooltip="'Company name'" class="flex-none">
                <span class="sr-only">Reference</span>
                <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width
                    aria-hidden='true' />
            </dt>
            <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="box_stats.fulfilment_customer.customer.contact_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="box_stats.fulfilment_customer.customer.company_name"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="box_stats.fulfilment_customer?.customer.email"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer?.customer.email }}</dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="box_stats.fulfilment_customer?.customer.phone"
                class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer?.customer.phone }}</dd>
            </div>
        </BoxStatsPalletDelivery>

        <!-- Box: Status -->
        <BoxStatsPalletDelivery class=" pb-2 py-5 px-3" :tooltip="trans('Status')" :label="capitalize(data?.data.state)"
            icon="fal fa-truck-couch">
            <div class="flex items-center w-full flex-none gap-x-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ box_stats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon='box_stats.delivery_status.icon' :class='box_stats.delivery_status.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.delivery_status.tooltip }}</dd>
            </div>
        </BoxStatsPalletDelivery>

        <!-- Box: Pallet -->
        <BoxStatsPalletDelivery class=" pb-2 py-5 px-3" :tooltip="trans('Pallets')" :percentage="0">
            <div class="flex items-end gap-x-3">
                <dt class="flex-none">
                    <span class="sr-only">Total pallet</span>
                    <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-none text-3xl font-medium">{{ data?.data.number_pallets }}</dd>
            </div>
        </BoxStatsPalletDelivery>

        <!-- Box: Barcode -->
        <BoxStatsPalletDelivery>
            <div class="h-full w-full px-2 flex flex-col items-center -mt-2 isolate">
                <svg id="palletDeliveryBarcode" class="w-full" />
                <div class="text-xxs md:text-xxs text-gray-500 -mt-1 z-10">
                    pad-{{ route().params.palletDelivery }}
                </div>
            </div>
        </BoxStatsPalletDelivery>
    </div>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :key="timeline.state" :data="props[currentTab]" :state="timeline.state" :tab="currentTab"
        :tableKey="tableKey" @renderTableKey="changeTableKey" :locationRoute="locationRoute"
        :storedItemsRoute="storedItemsRoute" :rentalRoute="rentalRoute" />

    <UploadExcel information="The list of column file: customer_reference, notes, stored_items"
        :propName="'pallet deliveries'" description="Adding Pallet Deliveries" :routes="{
        upload: get(dataModal, 'uploadRoutes', {}),
        download: props.uploadRoutes.download,
        history: props.uploadRoutes.history
    }" :dataModal="dataModal" />

    <!-- <pre>{{ props.pallets.data?.[0]?.reference }}</pre>
    <pre>{{ $inertia.page.props.queryBuilderProps.pallets.columns }}</pre> -->
</template>
