<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 20:46:53 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head,  } from '@inertiajs/vue3'
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { router } from '@inertiajs/vue3'
import type { Component } from 'vue'
import TableRentalAgreementClauses from "@/Components/Tables/Grp/Org/Fulfilment/TableRentalAgreementClauses.vue"
import { useTabChange } from "@/Composables/tab-change"
import { computed, defineAsyncComponent, inject, onMounted, onUnmounted, ref } from "vue"
import Tabs from "@/Components/Navigation/Tabs.vue"

import FulfilmentCustomerShowcase from "@/Components/Showcases/Grp/FulfilmentCustomerShowcase.vue"

import { Action } from '@/types/Action'

import { trans } from 'laravel-vue-i18n'
import {
    faStickyNote,
    faPallet,
    faUser,
    faNarwhal,
    faTruckCouch,
    faFileInvoiceDollar,
    faSignOutAlt,
    faPaperclip,
    faPaperPlane,
    faCheckDouble,
    faShare,
    faTruckLoading,
    faFileInvoice,
    faExclamationTriangle, faUsdCircle
} from '@fal'
import { notify } from '@kyvg/vue3-notification'
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading";
import type { Navigation } from "@/types/Tabs";
import Modal from "@/Components/Utils/Modal.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
library.add(faStickyNote, faUser, faNarwhal, faTruckCouch, faPallet, faFileInvoiceDollar, faSignOutAlt, faPaperclip, faPaperPlane, faCheckDouble, faShare, faTruckLoading, faFileInvoice, faExclamationTriangle, faUsdCircle)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes,
    tabs: {
        current: string
        navigation: Navigation;
    }
    showcase?: {}
    agreed_prices?: {}
    // invoices?: {}
    // recurringBills?: {}
    // pallets?: {}
    // stored_items?: {}
    // stored_item_returns?: {}
    // dispatched_emails?: {}
    // pallet_deliveries?: {}
    // pallet_returns?: {}
    // web_users?: {}
    // recurring_bills?: {}
    // webhook?: {}
    // history?: {}
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {

    const components: Component = {
        showcase: FulfilmentCustomerShowcase,
        agreed_prices: TableRentalAgreementClauses,
        // pallets: TablePallets,
        // stored_items: TableStoredItems,
        // stored_item_returns: TableStoredItemReturn,
        // pallet_deliveries: TablePalletDeliveries,
        // pallet_returns: TablePalletReturns,
        // invoices: TableInvoices,
        // details: ModelDetails,
        // web_users: TableWebUsers,
        // webhook: FulfilmentCustomerWebhook,
        // recurring_bills: TableRecurringBills,
        // history: TableHistories
    }

    return components[currentTab.value]

})



const isOpen = ref(false)
const warehouseValue = ref(null)
const errorMessage = ref(null)
const isModalOpen = ref(false)
const layout = inject('layout')
const loadingCreatePalletDelivery = ref(false)

// function setIsOpen(value) {
//     isOpen.value = value
// }

// const webUserForm = useForm({
//     // username: props["customer"].email,
//     username: null,
//     password: null,
// })


// const sendWarehouse = async (data: object) => {
//     try {
//         const response = await axios.post(
//             route(data.route?.name, data.route?.parameters),
//             { warehouse_id: get(warehouseValue.value, 'id') }
//         )
//         router.visit(route(response.data.route.name, response.data.route.parameters))
//     } catch (error) {
//         console.log('error', error)
//         errorMessage.value = error.response.data.message
//     }
// }


// const warehouseChange = (value) => {
//     errorMessage.value = null
//     warehouseValue.value = value
// }

const onButtonCreateDeliveryClick = (action: Action) => {
    console.log('action', action)
    if (action.disabled) isModalOpen.value = true
    
    else {
        router[action.route?.method || 'get'](
            route(action.route?.name || 'grp', action.route?.parameters),
            {},
            {
                onBefore: () => { loadingCreatePalletDelivery.value = true },
                onSuccess: () => {
                    console.log('on sucess aciton')
                },
                onError: (error: {}) => {
                    loadingCreatePalletDelivery.value = false,
                    notify({
                        title: 'Something went wrong.',
                        text: Object.values(error || {}).join(', '),
                        type: 'error',
                    })
                }
            }
        )
    }
}



onMounted(() => {
    window.Echo.private(`grp.${layout.group.id}.fulfilmentCustomer.${layout.user.id}`).listen('.PalletDelivery', (e) => {
        notify({
            title: e.data.title,
            text: e.data.text,
            type: "info"
        })
    })
})

onUnmounted(() => {
    window.Echo.private(`grp.${layout.group.id}.fulfilmentCustomer.${layout.user.id}`).stopListening('.PalletDelivery')
})



// console.log(props)

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!--   <template #button-create-delivery="{ action }">
            <div v-if="action.options.warehouses.data.length > 1" class="relative">
                <Popover :width="'w-full'" ref="_popover">
                    <template #button>
                        <Button :style="action.style" :label="action.label" :icon="action.icon"
                            :iconRight="action.iconRight"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip" />
                    </template>
<template #content="{ close: closed }">
                        <div class="w-[250px]">
                            <Multiselect v-model="warehouseValue" :searchable="true" :object="true" valueProp="id"
                                :options="action.options.warehouses.data" track-by="name" label="name"
                                @change="(value) => warehouseChange(value)" :mode="'single'" ref="multiselect"
                                placeholder="select a warehouse" class="w-full" />
                            <p v-if="errorMessage" class="mt-2 text-sm text-red-600" id="email-error">
                                {{ errorMessage }}
                            </p>
                            <div class="flex justify-end mt-3">
                                <Button :style="'save'" :label="'save'" @click="() => sendWarehouse(action)" />
                            </div>

                        </div>
                    </template>
</Popover>
</div>
<div v-else>
    <Link :href="route(action.route?.name, action.route?.parameters)" :method="'post'" :as="'button'">
    <Button :style="action.style" :label="action.label" :icon="action.icon"
        :iconRight="action.iconRight" :key="`ActionButton${action.label}${action.style}`"
        :tooltip="action.tooltip" />
    </Link>
</div>
</template> -->


        <template #button-delivery="{ action }">
            <Button
                @click="() => onButtonCreateDeliveryClick(action)"
                :style="action.style"
                :label="action.label"
                icon="fas fa-plus"
                :disabled="action.disabled || loadingCreatePalletDelivery"
                :iconRight="action.iconRight"
                :loading="loadingCreatePalletDelivery"
                :key="`ActionButton${action.label}${action.style}`"
                :tooltip="action.tooltip"
            />
        </template>

    </PageHeading>

    <!--     <TransitionRoot as="template" :show="isOpen">
        <Dialog :open="isOpen" @close="setIsOpen" as="div" class="relative z-10">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6">
                            <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">Create web user
                            </DialogTitle>

                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-4">
                                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                    <div class="mt-1">
                                        <input v-model="webUserForm.username" id="username" name="username" type="text"
                                            autocomplete="email"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot> -->

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>



    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-1/4">
        <div class="text-center">
            <font-awesome-icon :icon="['fal', 'exclamation-triangle']"  class="mx-auto h-12 w-12 text-gray-400"/>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">{{trans('You Dont Have Rental Agreement')}}</h3>
            <p class="mt-1 text-sm text-gray-500">{{trans('You need to make a rental agreement first to continue this')}}.</p>
            <div class="mt-6">
            </div>
        </div>
    </Modal>

</template>


<style src="@vueform/multiselect/themes/default.css"></style>

<style>
/* Style for multiselect globally */
.multiselect-option.is-selected,
.multiselect-option.is-selected.is-pointed {
    background: var(--ms-option-bg-selected, #6366f1) !important;
    color: var(--ms-option-color-selected, #fff) !important;
}

.multiselect-option.is-selected.is-disabled {
    background: var(--ms-option-bg-selected-disabled, #c7d2fe);
    color: var(--ms-option-color-selected-disabled, #818cf8);
}

.multiselect.is-active {
    border: var(--ms-border-width-active, var(--ms-border-width, 1px)) solid var(--ms-border-color-active, var(--ms-border-color, #d1d5db));
    box-shadow: 0 0 0 var(--ms-ring-width, 3px) var(--ms-ring-color, rgba(99, 102, 241, 0.188));
}
</style>
