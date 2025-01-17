<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { FulfilmentCustomer } from "@/types/Customer"
import { faCheck, faTimes } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'

import { useFormatTime } from '@/Composables/useFormatTime'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import StoredItemsProperty from '@/Components/StoredItemsProperty.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { trans } from 'laravel-vue-i18n'
import { reactive, ref } from 'vue'
import { debounce, get, set } from 'lodash'
import InputNumber from 'primevue/inputnumber'
import { notify } from '@kyvg/vue3-notification'
import CreateStoredItems from '@/Components/CreateStoredItems.vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'

import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import Icon from '@/Components/Icon.vue'
import { routeType } from '@/types/route'

import TableStoredItemsAudits from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemsAudits.vue"

import DataTable from "primevue/datatable"
import Column from "primevue/column"
import Tag from "@/Components/Tag.vue"

import { Pallet, PalletDelivery } from '@/types/Pallet'

import { faStickyNote, faCheckCircle as falCheckCircle, faUndo, faArrowToLeft, faTrashAlt } from '@fal'
import { faCheckCircle } from '@fad'
import { faPlus, faMinus, faStar } from '@fas'


library.add(faCheck, faTimes)

const props = defineProps<{
    data: {}
    tab?: string
    route_list: {
        update: routeType
        stored_item_audit_delta: {
            update: routeType  // Update quantity
            store: routeType  // add new stored item
            delete: routeType  // undo select
        }
    }
    storedItemsRoute: {
        index: routeType  // Fetch list of stored items
        store: routeType  // Add stored items
        delete: routeType  // Delete stored items
    }
}>()

const locale = inject('locale', aikuLocaleStructure)

function palletRoute(pallet: {}) {
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


// Helper to give color on change qty stored items
interface StoredItemsQuantity {
    [key: string]: {  // row index of Table
        [key: string]: number  // stored item id
    }
}
const storedItemsQuantity = reactive<StoredItemsQuantity>({})
const statesBoxEdit = reactive<StoredItemsQuantity>({})

const isLoading = ref(false)
const onCheck = (routex: routeType, store_item_id: number, qty: number) => {
    // router.post(route(routex.name, routex.parameters), {
    //     stored_item_ids	: {
    //         [store_item_id]: {
    //             quantity: qty
    //         }
    //     }
    // }, {
    //     onStart: () => {
    //         isLoading.value = true
    //     },
    //     onFinish: () => {
    //         isLoading.value = false
    //     }
    // })
}

// Unselect Audit Deltas
const isLoadingUnselect = reactive<StoredItemsQuantity>({})
const onUnselectNewStoredItem = (row: number, store_item_audit_deltas_id: number) => {
    props.route_list.stored_item_audit_delta.delete
    router.delete(
        route(props.route_list.stored_item_audit_delta.delete.name, store_item_audit_deltas_id),
        {
            onStart: () => {
                set(isLoadingUnselect, `${row}.${store_item_audit_deltas_id}`, true)
            },
            onFinish: () => {
                set(isLoadingUnselect, `${row}.${store_item_audit_deltas_id}`, false)
            }
        }
    )
}


// Section: store quantity stored item (first update)
const isLoadingStoreQuantity = reactive<StoredItemsQuantity>({})
const onStoreStoredItem = (row: number, idPallet: number, idStoredItemAudit: number, quantity: number) => {
    console.log('onStoreStoredItem')
    // Store
    console.log('lolo', props.route_list?.stored_item_audit_delta?.store?.name)
    if (!props.route_list?.stored_item_audit_delta?.store?.name) {
        console.error('No id stored item audit')
        return
    }

    router.post(
        route(props.route_list?.stored_item_audit_delta?.store.name, idStoredItemAudit),
        {
            pallet_id: idPallet,
            stored_item_id: idStoredItemAudit,
            audited_quantity: quantity
        },
        {
            preserveScroll: true,
            preserveState: true,
            onStart: () => {
                set(isLoadingStoreQuantity, `${row}.${idStoredItemAudit}`, true)
            },
            onError: (e) => {
                    console.error(e)
                    notify({
                        title: trans('Something went wrong.'),
                        text: trans('Failed to update the quantity.'),
                        type: 'error',
                    })
            },
            onFinish: () => {
                set(isLoadingStoreQuantity, `${row}.${idStoredItemAudit}`, false)
            }
        }
    )
}
const debounceStoreQuantity = debounce((row: number, idPallet: number, idStoredItemAudit: number, quantity: number) => onStoreStoredItem(row, idPallet, idStoredItemAudit, quantity), 500)


// Section: Update quantity stored item
const isLoadingQuantity = reactive<StoredItemsQuantity>({})
const onChangeQuantity = (row: number, idStoredItemAuditDelta: number | null, quantity: number) => {
    console.log('onChangeQuantity')
    //todo if   "store_item_audit_delta_id" ==null,
    // use props.route_list?.stored_item_audit_delta?.store.name
    // get back the store_item_audit_delta_id and set it uo so next time you call update
    
    // Update
    console.log('lolo', props.route_list?.stored_item_audit_delta?.update?.name)
    if (!props.route_list?.stored_item_audit_delta?.update?.name) {
        return
    }
    router.patch(
        route(props.route_list?.stored_item_audit_delta?.update.name, idStoredItemAuditDelta),
        {
            audited_quantity: quantity
        },
        {
            preserveScroll: true,
            preserveState: true,
            onStart: () => {
                set(isLoadingQuantity, `${row}.${idStoredItemAuditDelta}`, true)
            },
            onError: (e) => {
                    console.error(e)
                    notify({
                        title: trans('Something went wrong.'),
                        text: trans('Failed to update the quantity.'),
                        type: 'error',
                    })
            },
            onFinish: () => {
                set(isLoadingQuantity, `${row}.${idStoredItemAuditDelta}`, false)
            }
        }
    )
}
const debounceChangeQuantity = debounce((row: number, idStoredItemAuditDelta: number, quantity: number) => onChangeQuantity(row, idStoredItemAuditDelta, quantity), 500)


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5" striped rowAlignTop>

        <!-- Column: Reference -->
        <template #cell(reference)="{ item: pallet }">
            <component :is="pallet.slug ? Link : 'div'" :href="pallet.slug ? palletRoute(pallet) : undefined"
                :class="pallet.slug ? 'primaryLink' : ''">
                {{ pallet.reference }}
            </component>

            <div v-if="pallet.customer_reference" v-tooltip="trans('Customer\'s reference')"
                class="mt-1 space-x-1.5 whitespace-nowrap">
                <span class="text-gray-400 text-sm">({{ pallet.customer_reference }})</span>
            </div>
        </template>

        <!-- Column: Location -->
        <template #cell(location_code)="{ item: pallet }">
            <component :is="pallet.location_code ? Link : 'div'"
                :href="pallet.location_code ? route('grp.org.warehouses.show.infrastructure.locations.show', ['aw', pallet.warehouse_slug, pallet.location_slug]) : undefined"
                :class="pallet.slug ? 'secondaryLink' : ''">
                {{ pallet.location_code }}
            </component>
        </template>

        <!-- Column: Customer Reference -->
        <template #cell(customer_reference)="{ item: item }">
            <div class="">
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
            pallet id: {{ item.id }} <br />
            item.stored_item_audit_id: {{ item.stored_item_audit_id }} <br />
            route store: {{ props.route_list?.stored_item_audit_delta?.store.name }} <br />

            <DataTable v-if="proxyItem.stored_items?.length || proxyItem.new_stored_items?.length"
                :value="[...proxyItem.stored_items, ...proxyItem.new_stored_items]">
                <Column field="reference" :header="trans('SKU')" class="">
                    <template #body="{ data }">
                        <div class="whitespace-nowrap">{{ data.reference }}
                            <FontAwesomeIcon v-if="data.type === 'new_item'" v-tooltip="trans('New added Customer\'s SKU')" icon='fas fa-star' size="xs" class='text-indigo-500' fixed-width aria-hidden='true' />
                        </div>
                    </template>
                </Column>

                <Column field="quantity" header="Current qty" class="">
                    <template #body="{ data }">
                        <div class="text-right">{{ data.quantity || '' }}</div>
                    </template>
                </Column>

                <Column field="quantity" header="Actions" class="">


                    <template #body="{ data }">

                        stored item id: {{ data.id || '-' }} <br />
                        storedItemAuditDelta: {{ data.stored_item_audit_delta || '-' }}

                        <!-- <pre>{{ props.route_list?.stored_item_audit_delta?.store }}</pre> -->
                        <!-- <pre>{{ data }}</pre> -->
                        <div class="relative">
                            <div v-if="get(isLoadingQuantity, [item.rowIndex, data.stored_item_audit_delta], false) || get(isLoadingUnselect, [item.rowIndex, data.stored_item_audit_delta], false)"
                                class="z-10 opacity-60 absolute w-full h-full top-0 left-0">
                                <div class="skeleton h-full w-full"></div>
                            </div>

                            <div class="flex gap-x-2.5 items-center w-64">
                                <div class="flex justify-center border border-gray-300 rounded gap-y-1">
                                    <!-- Button: Check -->
                                    <Button v-if="data.type !== 'new_item' && !data.stored_item_audit_delta"
                                        @click="() => onStoreStoredItem(item.rowIndex, item.id, data.id, data.quantity)"
                                        type="tertiary" icon="fal fa-check-circle" class="border-none rounded-none"
                                    />

                                    <!-- Section: - and + -->
                                    <div class="transition-all relative inline-flex items-center justify-center "
                                        :class="!get('statesBoxEdit', `${item.rowIndex}.${data.id}`, false) ? 'w-28' : 'w-14'">
                                        <transition>
                                            <div v-if="!get('statesBoxEdit', `${item.rowIndex}.${data.id}`, false)"
                                                class="relative flex flex-nowrap items-center justify-center gap-y-1 gap-x-1">
                                                <!-- Button: Minus -->
                                                <div  @click="() => (
                                                        set(data, `${data.stored_item_audit_delta ? 'audited_quantity' : 'quantity'}`, ((data.stored_item_audit_delta ? data.audited_quantity : data.quantity) - 1) >= 0 ? ((data.stored_item_audit_delta ? data.audited_quantity : data.quantity) - 1) : 0),
                                                        data.stored_item_audit_delta
                                                            ? debounceChangeQuantity(item.rowIndex, data.stored_item_audit_delta, get(data, `audited_quantity`, data.quantity))
                                                            : debounceStoreQuantity(item.rowIndex, item.id, data.id, get(data, `quantity`, data.quantity))
                                                    )"
                                                    class="leading-4 cursor-pointer inline-flex items-center gap-x-2 font-medium focus:outline-none disabled:cursor-not-allowed min-w-max bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-200/70 disabled:bg-gray-200/70 rounded px-1 py-1.5 text-xs justify-self-center">
                                                    <FontAwesomeIcon icon='fas fa-minus' class='' fixed-width aria-hidden='true' />
                                                </div>

                                                <div class="text-center tabular-nums border border-transparent hover:border-dashed hover:border-gray-300 group-focus:border-dashed group-focus:border-gray-300">
                                                    
                                                    <InputNumber
                                                        :modelValue="data.stored_item_audit_delta ? data.audited_quantity : data.quantity"
                                                        @update:modelValue="(e) => debounceChangeQuantity(item.rowIndex, data.stored_item_audit_delta, e)"
                                                        buttonLayout="horizontal" :min="0" style="width: 100%"
                                                        :inputStyle="{
                                                            padding: '0px',
                                                            width: '50px',
                                                            coloxxr: data.audited_quantity > data.quantity ? '#00d200' : data.audited_quantity === data.quantity ? 'gray' : 'red'
                                                        }" />
                                                </div>

                                                <!-- Button: Plus -->
                                                <div  @click="() => (
                                                        set(data, `${data.stored_item_audit_delta ? 'audited_quantity' : 'quantity'}`, (data.stored_item_audit_delta ? data.audited_quantity : data.quantity) + 1),
                                                        data.stored_item_audit_delta
                                                            ? debounceChangeQuantity(item.rowIndex, data.stored_item_audit_delta, get(data, `audited_quantity`, data.quantity))
                                                            : debounceStoreQuantity(item.rowIndex, item.id, data.id, get(data, `quantity`, data.quantity))
                                                    )"
                                                    type="tertiary" size="xs"
                                                    class="leading-4 cursor-pointer inline-flex items-center gap-x-2 font-medium focus:outline-none disabled:cursor-not-allowed min-w-max bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-200/70 disabled:bg-gray-200/70 rounded px-1 py-1.5 text-xs justify-self-center">
                                                    <FontAwesomeIcon icon='fas fa-plus' class='' fixed-width
                                                        aria-hidden='true' />
                                                </div>
                                            </div>

                                            <div v-else @click="set(statesBoxEdit, `zzz${item.rowIndex}.${data.id}`, true)"
                                                class="hover:bg-gray-200 text-gray-400 hover:text-gray-600 w-full flex justify-center items-center h-full cursor-pointer px-2 gap-x-1">
                                                <span class="text-gray-600">
                                                  {{!data.stored_item_audit_delta_id ?  data.quantity :  data.audited_quantity }}
                                                </span>
                                                <FontAwesomeIcon v-tooltip="trans('Edit')" icon='fal fa-pencil' size="sm" class=''
                                                    fixed-width aria-hidden='true' />
                                            </div>
                                        </transition>
                                    </div>

                                    <!-- Button: Reset -->
                                    <Button
                                        v-if="data.type === 'new_item'"
                                        @click="() => onUnselectNewStoredItem(item.rowIndex, data.stored_item_audit_delta)"
                                        type="tertiary"
                                        icon="fal fa-trash-alt"
                                        class="border-none rounded-none text-red-500"
                                        :loading="!!get(isLoadingUnselect, [item.rowIndex, data.stored_item_audit_delta], false)"
                                    />

                                  <Button
                                    v-else-if="   data.stored_item_audit_delta"
                                    @click="() => (
                                            set(data, audited_quantity, get(data, audited_quantity, data.quantity)),
                                            data.storedItemAuditDelta
                                                ? debounceChangeQuantity(item.rowIndex, data.storedItemAuditDelta, get(data, audited_quantity, data.quantity))
                                                : debounceStoreQuantity(item.rowIndex, item.id, data.id, get(data, audited_quantity, data.quantity))
                                        )"
                                    type="tertiary"
                                    icon="fal fa-undo"
                                    class="border-none rounded-none"
                                    :loading="!!get(isLoadingUnselect, [item.rowIndex, data.storedItemAuditDelta], false)"
                                  />
                                </div>

                                <!-- <FontAwesomeIcon v-tooltip="trans('Close')"
                                    @click="() => set(statesBoxEdit, `${item.rowIndex}.${data.id}`, false)"
                                    icon='fal fa-arrow-to-left'
                                    class='py-1 px-1 transition-all cursor-pointer text-gray-400 hover:text-gray-700'
                                    :class="get(statesBoxEdit, `${item.rowIndex}.${data.id}`, false) ? '' : 'hidden'"
                                    fixed-width aria-hidden='true' /> -->
                            </div>
                        </div>
                    </template>
                </Column>

                <!-- <Column field="quantity" header="Checked" class="text-right"> -->
                <template #body="{ data }">
                    <template v-if="data.type === 'new_item'">
                        <div v-if="get(isLoadingUnselect, [item.rowIndex, data.stored_item_audit_delta], false)"
                            class="text-center text-red-500">
                            <LoadingIcon class="" />
                        </div>
                        <div v-else @click="() => onUnselectNewStoredItem(item.rowIndex, data.stored_item_audit_delta)"
                            class="text-red-500 hover:underline cursor-pointer">
                            {{ trans("Unselect") }}
                        </div>
                    </template>
                    <div v-else @click="onCheck(proxyItem.auditRoute, data.id, data.quantity)"
                        class="mx-auto cursor-pointer w-fit py-0.5 px-3 text-gray-500 hover:text-green-500">
                        <FontAwesomeIcon icon='fad fa-check-circle' class='' fixed-width aria-hidden='true' />
                    </div>
                </template>
                <!-- </Column> -->

                <!-- <Column field="quantity" header="">
            <template #body="{ data }">
            </template>
        </Column> -->
            </DataTable>

            <div v-else class="text-gray-400">

            </div>

        </template>

        <template #cell(actions)="{ item }">
            <StoredItemsProperty
                :pallet="item"
                :storedItemsRoute
                :editable="true"
                :saveRoute="route_list.stored_item_audit_delta.store"
                class="mt-2"
            >
                <template #default="{ openModal }">
                    <Button @click="openModal" type="dashed" icon="fas fa-plus" fuxll
                        :label="trans('Customer\'s SKU')" />
                </template>

                <template #modal="{ form, sendToServer, closeModal }">
                    <!-- <pre>{{ item.id }}</pre> -->
                    <CreateStoredItems :storedItemsRoute="storedItemsRoute" :form="form" @onSave="() => sendToServer(
                        {
                            pallet_id: item.id,
                            stored_item_id: form.id,
                            audited_quantity: form.quantity,
                        },
                        true
                    )" :stored_items="item.stored_items" @closeModal="closeModal" title="Add Customer's SKU" />
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
</template>
