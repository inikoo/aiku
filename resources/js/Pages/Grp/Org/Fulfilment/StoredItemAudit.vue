<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 23 May 2024 15:57:55 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import BoxNote from "@/Components/Pallet/BoxNote.vue"
import BoxAuditStoredItems from '@/Components/Box/BoxAuditStoredItems.vue'

import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"
import { library } from "@fortawesome/fontawesome-svg-core"
import TableStoredItemsAudits from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemsAudits.vue"

import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Tag from "@/Components/Tag.vue"

import { Pallet, PalletDelivery } from '@/types/Pallet'
import { routeType } from "@/types/route"

import { faStickyNote, faPlus, faMinus } from '@fal'
import { faCheckCircle } from '@fad'
import { faStar } from '@fas'
import Table from '@/Components/Table/Table.vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Icon from '@/Components/Icon.vue'
import StoredItemsProperty from '@/Components/StoredItemsProperty.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { trans } from 'laravel-vue-i18n'
import { reactive, ref } from 'vue'
import { get, set } from 'lodash'
// import QuantityInput from '@/Components/Utils/QuantityInput.vue'
library.add(faStickyNote, faPlus, faMinus, faCheckCircle, faStar)

const props = defineProps<{
    data: {
        data: PalletDelivery
    }
    storedItemsRoute: {
        store: routeType
        index: routeType
        delete: routeType
    }
    title: string
    pageHead: PageHeadingTypes
    notes_data: any
    edit_stored_item_deltas: any
    fulfilment_customer: any
    route_list: {
        update: routeType
    }
}>()
console.log(props)

function palletRoute(pallet: Pallet) {
    switch (route().current()) {
        case "grp.overview.fulfilment.pallets.index":
            return route(
                "grp.org.fulfilments.show.operations.pallets.current.show",
                [
                    pallet.organisation_slug,
                    pallet.fulfilment_slug,
                    pallet.slug
                ])
        case "grp.org.fulfilments.show.operations.pallets.current.index":
            return route(
                "grp.org.fulfilments.show.operations.pallets.current.show",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    pallet.slug
                ])

        case "grp.org.fulfilments.show.operations.returned_pallets.index":
            return route(
                "grp.org.fulfilments.show.operations.returned_pallets.index",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    pallet.slug
                ])

        case "grp.org.fulfilments.show.crm.customers.show":
            return route(
                "grp.org.fulfilments.show.crm.customers.show.pallets.show",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    route().params["fulfilmentCustomer"],
                    pallet.slug
                ])
        case "grp.org.fulfilments.show.crm.customers.show.stored-item-audits.show":
            return route(
                "grp.org.fulfilments.show.crm.customers.show.pallets.show",
                [
                    route().params["organisation"],
                    route().params["fulfilment"],
                    route().params["fulfilmentCustomer"],
                    pallet.slug
                ])

        default:
            return []
    }
}


const isLoading = ref(false)
const onCheck = (routex: routeType, store_item_id: number, qty: number) => {
    router.post(route(routex.name, routex.parameters), {
        stored_item_ids	: {
            [store_item_id]: {
                quantity: qty
            }
        }
    }, {
        onStart: () => {
            isLoading.value = true
        },
        onFinish: () => {
            isLoading.value = false
        }
    })
}

// Helper to give color on change qty stored items
interface StoredItemsQuantity {
    [key: string]: {  // row index of Table
        [key: string]: number  // stored item id
    }
}
const storedItemsQuantity = reactive<StoredItemsQuantity>({
})
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <div class="grid grid-cols-2 h-fit lg:max-h-64 w-full lg:justify-center border-b border-gray-300">
        <BoxNote v-for="(note, index) in notes_data" :key="index + note.label" :noteData="note"
            :updateRoute="route.update" />
    </div>
    <BoxAuditStoredItems :auditData="data.data" :boxStats="fulfilment_customer" />
    <!-- <TableStoredItemsAudits :data="edit_stored_item_deltas" tab="edit_stored_item_deltas" :storedItemsRoute="storedItemsRoute" /> -->

    <Table :resource="edit_stored_item_deltas" name="edit_stored_item_deltas" class="mt-5">
        <!-- Column: Reference -->
        <template #cell(reference)="{ item: pallet }">
            <component :is="pallet.slug ? Link : 'div'" :href="pallet.slug ? palletRoute(pallet) : undefined"
                :class="pallet.slug ? 'primaryLink' : ''">
                {{ pallet.reference }}
            </component>
        </template>



        <!-- Column: Customer Reference -->
        <template #cell(customer_reference)="{ item: item }">
            <div>
                {{ item.customer_reference }}
                <span v-if="item.notes" class="text-gray-400 text-xs ml-1">
                    <FontAwesomeIcon icon="fal fa-sticky-note" class="text-gray-400" fixed-width aria-hidden="true" />
                    {{ item.notes }}
                </span>
            </div>
        </template>


        <!-- Column: Icon (status and state) -->
        <template #cell(state)="{ item: pallet }">
            <div class="flex gap-x-1 gap-y-1.5">
                <Icon :data="pallet.type_icon" class="px-1" />
                <Icon :data="pallet['status_icon']" />
                <Icon :data="pallet['state_icon']" />
            </div>
        </template>


        <!-- Column: Notes -->
        <template #cell(notes)="{ item: pallet }">
            <div class="text-gray-500 italic">{{ pallet.notes }}</div>
        </template>


        <!-- Column: Customer SKUS -->
        <template #cell(stored_items)="{ proxyItem, item }">

            <pre>{{ proxyItem.stored_items }}</pre>
            <DataTable v-if="proxyItem.stored_items?.length" :value="[...proxyItem.stored_items, ...proxyItem.new_stored_items]">
                <Column field="reference" :header="trans('SKU')">
                    <template #body="{ data }">
                        {{ data.reference }} <FontAwesomeIcon v-if="data.type === 'new_item'" v-tooltip="trans('New stored item')" icon='fas fa-star' class='text-indigo-500' fixed-width aria-hidden='true' />
                    </template>
                </Column>

                <Column field="quantity" header="Current qty">
                    <template #body="{ data }">
                        {{ data.quantity }}
                    </template>
                </Column>

                <Column field="quantity" header="" class="w-36">
                    <template #body="{ data }">

                        <!-- <QuantityInput
                            v-model="data.audited_quantity"
                            :quantity_from="data.quantity"
                            :datxa="{
                                id: 4,
                                quantity_ordered: 5,
                                inputTriggered: true
                            }"
                        /> -->

                        <div class="grid lg:grid-cols-3 gap-y-1 gap-x-1">
                            <Button @click="() => set(data, `audited_quantity`, get(data, `audited_quantity`, data.quantity) - 1)" icon="fal fa-minus" :loading="isLoading" type="tertiary" size="xs" class="justify-self-center" />
                                <div class="text-center tabular-nums">
                                <Transition name="spin-to-right">
                                    <span :key="data.audited_quantity" class="text-lg" :class="data.audited_quantity > data.quantity ? 'text-green-500' : data.audited_quantity === data.quantity ? 'text-gray-500' : 'text-red-500'">
                                        {{ data.audited_quantity }}
                                    </span>
                                </Transition>
                            </div>
                            <Button @click="() => set(data, `audited_quantity`, get(data, `audited_quantity`, data.quantity) + 1)" icon="fal fa-plus" :loading="isLoading" type="tertiary" size="xs" class="justify-self-center" />
                        </div>
                    </template>
                </Column>

                <Column field="quantity" header="Checked" class="text-right">
                    <template #body="{ data }">
                        <div v-if="data.type === 'new_item'">
                            
                        </div>
                        <div v-else @click="onCheck(proxyItem.auditRoute, data.id, data.quantity)" class="mx-auto cursor-pointer w-fit py-0.5 px-3 text-gray-500 hover:text-green-500">
                            <FontAwesomeIcon icon='fad fa-check-circle'
                                class='' fixed-width aria-hidden='true' />
                        </div>
                    </template>
                </Column>

                <!-- <Column field="quantity" header="">
                    <template #body="{ data }">
                    </template>
                </Column> -->
            </DataTable>

            <div v-else class="text-gray-400">
                
            </div>

            <pre>{{ proxyItem.new_stored_items }}</pre>
            
            
            <!-- <pre>{{ proxyItem.new_stored_items }}</pre>
            <DataTable v-if="proxyItem.new_stored_items?.length" :value="proxyItem.new_stored_items">
                <Column field="reference" :header="trans('SKU')">
                </Column>

                <Column field="quantity" header="Current qty">
                    <template #body="{ data }">
                        {{ data.quantity }}
                    </template>
                </Column>

                <Column field="quantity" header="" class="w-36">
                    <template #body="{ data }">
                        <div class="grid lg:grid-cols-3 gap-y-1 gap-x-1">
                            <Button @click="() => set(storedItemsQuantity, `${item.rowIndex}.${data.id}`, get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) - 1)" icon="fal fa-minus" :loading="isLoading" type="tertiary" size="xs" class="justify-self-center" />
                                <div class="text-center tabular-nums">
                                <Transition name="spin-to-right">
                                    <span :key="get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity)" class="text-lg" :class="get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) > data.quantity ? 'text-green-500' : get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) === data.quantity ? 'text-gray-500' : 'text-red-500'">
                                        {{ get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) }}
                                    </span>
                                </Transition>
                            </div>
                            <Button @click="() => set(storedItemsQuantity, `${item.rowIndex}.${data.id}`, get(storedItemsQuantity, `${item.rowIndex}.${data.id}`, data.quantity) + 1)" icon="fal fa-plus" :loading="isLoading" type="tertiary" size="xs" class="justify-self-center" />
                        </div>
                    </template>
                </Column>

                <Column field="quantity" header="Checked" class="text-right">
                    <template #body="{ data }">
                        <div @click="onCheck(proxyItem.auditRoute, data.id, data.quantity)" class="mx-auto cursor-pointer w-fit py-0.5 px-3 text-gray-500 hover:text-green-500">
                            <FontAwesomeIcon icon='fad fa-check-circle'
                                class='' fixed-width aria-hidden='true' />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <div v-else class="text-gray-400">
                
            </div> -->
            
            <StoredItemsProperty
                :pallet="item"
                :storedItemsRoute="storedItemsRoute"
                :editable="true"
                :saveRoute="item.auditRoute"
                class="mt-2"
                title="Add stored item"
            >
                <template #default="{ openModal }">
                    <Button @click="openModal" type="dashed" icon="fal fa-plus" full label="Add stored item" />
                </template>
            </StoredItemsProperty>
        </template>

        <!-- Column: edited -->
        <template #cell(audits)="{ item }">

            <!-- <StoredItemsProperty :pallet="item" :storedItemsRoute="storedItemsRoute" :editable="true"
                :saveRoute="item.auditRoute" /> -->

            <!-- <div v-if="item.audited_at" class="flex items-center justify-center">
                <font-awesome-icon :icon="['fas', 'check-circle']" class="text-lg text-green-500 mr-2"
                    v-tooltip="`Audited at: ${useFormatTime(item.audited_at)}`" />

                <Popover>
                    <template #button="{ isOpen }">
                        <Button label="Undo" type="tertiary" size="xs" icon="fal fa-history" />
                    </template>

                    <template #content="{ open, close }">
                        <div class="font-bold text-xs mb-3">are you sure to undo the Audit ?</div>
                        <div class="flex justify-end gap-1">
                            <Button label="No" type="tertiary" size="xs" @click="close()" />
                            <Link :href="route(item.resetAuditRoute.name, item.resetAuditRoute.parameters)"
                                method="delete" type="button" preserve-scroll>
                            <Button label="Yes" size="xs" />
                            </Link>
                        </div>
                    </template>
                </Popover>


            </div> -->
        </template>


    </Table>
    {{ storedItemsQuantity }}

</template>
