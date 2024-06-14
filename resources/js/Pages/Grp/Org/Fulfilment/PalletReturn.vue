<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head, Link, useForm} from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, onMounted, ref, watch, inject } from 'vue'
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import TablePalletReturn from "@/Components/PalletReturn/tablePalletReturn.vue"
import TablePalletReturnsDelivery from "@/Components/Tables/Grp/Org/Fulfilment/TablePalletReturnPallets.vue"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import palletReturnDescriptor from "@/Components/PalletReturn/Descriptor/PalletReturn"
import Tag from "@/Components/Tag.vue"
import BoxStatPallet from "@/Components/Pallet/BoxStatPallet.vue"
import JsBarcode from "jsbarcode"
import { BoxStats, PDRNotes } from '@/types/Pallet'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faIdCardAlt, faUser, faBuilding, faEnvelope, faPhone, faMapMarkerAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { trans } from "laravel-vue-i18n"
import TableServices from "@/Components/Tables/Grp/Org/Fulfilment/TableServices.vue";
import TablePhysicalGoods from "@/Components/Tables/Grp/Org/Fulfilment/TablePhysicalGoods.vue";
import {get} from "lodash";
import PureInput from "@/Components/Pure/PureInput.vue";
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue";
import Popover from "@/Components/Popover.vue";

library.add(faIdCardAlt, faUser, faBuilding, faEnvelope, faPhone, faMapMarkerAlt )

const layout = inject('layout', {})

const props = defineProps<{
    title: string
    tabs: {}
    pallets?: {}
    services?: {}
    service_lists?: {}
    physical_good_lists?: {}
    physical_goods?: {}
    data?: {}
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

// console.log('qwewqewq', props.box_stats)

let currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const timeline = ref({ ...props.data.data })
const openModal = ref(false)
const loading = ref(false)

const formAddService = useForm({ service_id: '', quantity: 1 })
const formAddPhysicalGood = useForm({ outer_id: '', quantity: 1 })

const component = computed(() => {
    const components = {
        pallets: TablePalletReturnsDelivery,
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

onMounted(() => {
    JsBarcode('#palletReturnBarcode', 'par-' + route().v().params.palletDelivery, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: '50%',
        displayValue: false
    })
})

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
        <template #button-group-add-pallet="{ action: action }">
            <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
                :iconRight="action.button.iconRight" :key="`ActionButton${action.button.label}${action.button.style}`"
                :tooltip="action.button.tooltip" @click="() => (openModal = true)" />
        </template>

        <!-- Button: Add service (single) -->
        <template #button-group-add-service="{ action: action }">
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
        <template #button-group-add-physical-good="{ action: action }">
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

    <!-- Section: Box -->
    <div class="h-min grid grid-cols-4 border-b border-gray-200 divide-x divide-gray-300">
        <!-- Box: Customer -->
        <BoxStatPallet class="py-2 px-3">
            <!-- Field: Reference -->
            <Link as="a" v-if="box_stats.fulfilment_customer.customer.reference"
                :href="route('grp.org.fulfilments.show.crm.customers.show', [route().params.organisation, box_stats.fulfilment_customer.fulfilment.slug, box_stats.fulfilment_customer.slug])"
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer secondaryLink">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Reference</span>
                    <FontAwesomeIcon icon='fal fa-id-card-alt' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.reference }}</dd>
            </Link>

            <!-- Field: Contact name -->
            <div v-if="box_stats.fulfilment_customer.customer.contact_name" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Contact name'" class="flex-none">
                    <span class="sr-only">Contact name</span>
                    <FontAwesomeIcon icon='fal fa-user' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.contact_name }}</dd>
            </div>


            <!-- Field: Company name -->
            <div v-if="box_stats.fulfilment_customer.customer.company_name" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Company name'" class="flex-none">
                    <span class="sr-only">Company name</span>
                    <FontAwesomeIcon icon='fal fa-building' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer.customer.company_name }}</dd>
            </div>

            <!-- Field: Email -->
            <div v-if="box_stats.fulfilment_customer?.customer.email" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Email'" class="flex-none">
                    <span class="sr-only">Email</span>
                    <FontAwesomeIcon icon='fal fa-envelope' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer?.customer.email }}</dd>
            </div>

            <!-- Field: Phone -->
            <div v-if="box_stats.fulfilment_customer?.customer.phone" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Phone</span>
                    <FontAwesomeIcon icon='fal fa-phone' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer?.customer.phone }}</dd>
            </div>

            <!-- Field: Location -->
            <div v-if="box_stats.fulfilment_customer?.customer?.location?.length" class="flex items-center w-full flex-none gap-x-2">
                <dt v-tooltip="'Phone'" class="flex-none">
                    <span class="sr-only">Location</span>
                    <FontAwesomeIcon icon='fal fa-map-marker-alt' size="xs" class='text-gray-400' fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.fulfilment_customer?.customer.location.join(", ") }}</dd>
            </div>
        </BoxStatPallet>


        <BoxStatPallet class="py-2 px-3" :label="capitalize(data?.data.state)"
            icon="fal fa-truck-couch">
            <div class="flex items-center w-full flex-none gap-x-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ box_stats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon='box_stats.delivery_status.icon' :class='box_stats.delivery_status.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.delivery_status.tooltip }}</dd>
            </div>
        </BoxStatPallet>

        <!-- Box: Pallet -->
        <BoxStatPallet class="py-2 px-3" :percentage="0">
            <div class="flex items-end gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Total pallet</span>
                    <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium ">{{ data?.data.number_pallets }}</dd>
            </div>

            <div class="flex items-end gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Services</span>
                    <FontAwesomeIcon icon='fal fa-concierge-bell' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium">{{ data?.data.number_pallets }}</dd>
            </div>

            <div class="flex items-end gap-x-3 mb-1">
                <dt class="flex-none">
                    <span class="sr-only">Physical Goods</span>
                    <FontAwesomeIcon icon='fal fa-cube' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-6 text-lg font-medium">{{ data?.data.number_pallets }}</dd>
            </div>

        </BoxStatPallet>


        <!-- Box: Barcode -->
        <BoxStatPallet>
            <div class="h-full w-full px-2 flex flex-col items-center -mt-2">
                <svg id="palletReturnBarcode" class="w-full" />
                <div class="text-xxs md:text-xxs text-gray-500 -mt-1">
                    par-{{ route().params.palletReturn }}
                </div>
            </div>
        </BoxStatPallet>
    </div>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :state="timeline.state" :key="timeline.state" :tab="currentTab" />

    <Modal :isOpen="openModal" @onClose="openModal = false">
        <div class="min-h-72 max-h-96 px-2 overflow-auto">
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
