<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 20:46:53 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faCodeCommit,
    faGlobe,
    faGraduationCap,
    faMoneyBill,
    faPaperclip, faPaperPlane, faStickyNote,
    faTags, faCube, faCodeBranch, faShoppingCart, faNarwhal, faTruck
} from '@fal';
import ModelDetails from "@/Components/ModelDetails.vue";
import TableOrders from "@/Components/Tables/TableOrders.vue";
import { useTabChange } from "@/Composables/tab-change";
import { computed, defineAsyncComponent, ref } from "vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TablePalletDeliveries from '@/Components/Tables/Grp/Org/Fulfilment/CRM/TablePalletDeliveries.vue';
import Popover from '@/Components/Popover.vue';
import CustomerShowcase from "@/Components/Showcases/Grp/CustomerShowcase.vue";
import Button from '@/Components/Elements/Buttons/Button.vue'
import Multiselect from "@vueform/multiselect"
import { Link } from "@inertiajs/vue3"
import { get } from 'lodash';
import axios from 'axios';

import TableDispatchedEmails from "@/Components/Tables/TableDispatchedEmails.vue";

library.add(
    faStickyNote,
    faGlobe,
    faMoneyBill,
    faGraduationCap,
    faTags,
    faCodeCommit,
    faPaperclip,
    faPaperPlane,
    faCube,
    faCodeBranch,
    faShoppingCart,
    faNarwhal,
    faTruck
)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    showcase?: object
    orders?: object
    stored_items?: object
    dispatched_emails?: object
    pallet_deliveries?: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        showcase: CustomerShowcase,
        stored_items: TableStoredItems,
        orders: TableOrders,
        details: ModelDetails,
        history: ModelChangelog,
        dispatched_emails: TableDispatchedEmails,
        pallet_deliveries: TablePalletDeliveries

    };
    return components[currentTab.value];

});

import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import AgentShowcase from "@/Pages/Procurement/AgentShowcase.vue";
import TableStoredItems from "@/Components/Tables/TableStoredItems.vue";


const isOpen = ref(false);
const warehouseValue = ref(null)
const errorMessage = ref(null)

function setIsOpen(value) {
    isOpen.value = value;
}

const webUserForm = useForm({
    // username: props["customer"].email,
    username: null,
    password: null,
});


const sendWarehouse = async (data : object) => {
        try {
            const response = await axios.post(
                route(data.route?.name, data.route?.parameters),
                { warehouse_id: get(warehouseValue.value,'id') }
            );
            
        } catch (error) {
            console.log('error',error)
            errorMessage.value = error.response.data.message
        }
    }


    const warehouseChange = (value) => {
        errorMessage.value = null
        warehouseValue.value = value
    }


</script>

<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-create-delivery="{ action: action }">
            <div class="relative">
                <Popover :width="'w-full'" ref="_popover">
                    <template #button>
                        <Button :style="action.action.style" :label="action.action.label" :icon="action.action.icon"
                            :iconRight="action.action.iconRight"
                            :key="`ActionButton${action.action.label}${action.action.style}`"
                            :tooltip="action.action.tooltip" />
                    </template>
                    <template #content="{ close: closed }">
                        <div class="w-[250px]">
                            <Multiselect v-model="warehouseValue" :searchable="true" :object="true" valueProp="id"
                                :options="action.action.options.warehouses.data" track-by="name" label="name" @change="(value)=>warehouseChange(value)"
                                :mode="'single'" ref="multiselect" placeholder="select a warehouse" class="w-full"/>
                                <p v-if="errorMessage" class="mt-2 text-sm text-red-600" id="email-error">
			                        {{ errorMessage }}
		                        </p>
                            <div class="flex justify-end mt-3">

                              <!--   <Link :href="route(action.action.route?.name, action.action.route?.parameters)" method="post"
                                :as="'button'" :data="{x : get(warehouseValue,'id')}" :canClear="false"> -->

                                <Button :style="'save'" :label="'save'" @click="()=>sendWarehouse(action.action)" />
                           <!--  </Link> -->
                            </div>
                           
                        </div>
                    </template>
                </Popover>
            </div>

        </template>
    </PageHeading>
    <!--
      Todo: modal forms for quick creation of models
      -->

    <TransitionRoot as="template" :show="isOpen">
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
    </TransitionRoot>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
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