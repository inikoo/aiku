<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, onMounted, ref, watch, inject } from 'vue'
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/TableHistories.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import TablePalletReturn from "@/Components/PalletReturn/tablePalletReturn.vue"
import TablePalletReturnsDelivery from "@/Components/Tables/TablePalletReturnsDelivery.vue"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import palletReturnDescriptor from "@/Components/PalletReturn/Descriptor/PalletReturn"
import Tag from "@/Components/Tag.vue"
import BoxStatsPalletDelivery from "@/Components/Pallet/BoxStatsPalletDelivery.vue"
import JsBarcode from "jsbarcode"
import { BoxStats, PDRNotes } from '@/types/Pallet'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faIdCardAlt, faUser, faBuilding, faEnvelope, faPhone, faMapMarkerAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import PureTextarea from "@/Components/Pure/PureTextarea.vue"
import { trans } from "laravel-vue-i18n"
library.add(faIdCardAlt, faUser, faBuilding, faEnvelope, faPhone, faMapMarkerAlt )

const layout = inject('layout', {})

const props = defineProps<{
    title: string
    tabs: {}
    pallets?: {}
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

onMounted(() => {
    JsBarcode('#palletReturnBarcode', 'par-' + route().v().params.palletDelivery, {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: '50%',
        displayValue: false
    })
})

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
        <BoxStatsPalletDelivery class="pb-2 pt-5 px-3" tooltip="Customer">
            <!-- Field: Reference -->
            <Link as="a" v-if="box_stats.fulfilment_customer.customer.reference"
                :href="route('grp.org.fulfilments.show.crm.customers.show', [route().params.organisation, box_stats.fulfilment_customer.fulfilment.slug, box_stats.fulfilment_customer.slug])" 
                class="flex items-center w-fit flex-none gap-x-2 cursor-pointer specialUnderlineSecondary">
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
        </BoxStatsPalletDelivery>

        <!-- Box: Barcode -->
        <BoxStatsPalletDelivery>
            <div class="h-full w-full px-2 flex flex-col items-center -mt-2">
                <svg id="palletReturnBarcode" class="w-full" />
                <div class="text-xxs md:text-xxs text-gray-500 -mt-1">
                    par-{{ route().params.palletReturn }}
                </div>
            </div>
        </BoxStatsPalletDelivery>
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
