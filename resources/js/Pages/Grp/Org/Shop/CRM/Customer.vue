<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 20:46:53 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head, useForm} from '@inertiajs/vue3';
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faCodeCommit,
    faGlobe,
    faGraduationCap,
    faMoneyBill,
    faPaperclip, faPaperPlane, faStickyNote,
    faTags,faCube,faCodeBranch,faShoppingCart
} from '@fal';
import ModelDetails from "@/Components/ModelDetails.vue";
import TableOrders from "@/Components/Tables/Grp/Org/Ordering/TableOrders.vue";
import {useTabChange} from "@/Composables/tab-change";
import {computed, defineAsyncComponent, ref} from "vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableProducts from "@/Components/Tables/Grp/Org/Catalogue/TableProducts.vue";
import CustomerShowcase from "@/Components/Showcases/Grp/CustomerShowcase.vue";

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
    faShoppingCart
)

const ModelChangelog = defineAsyncComponent(() => import('@/Components/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    showcase?:object
    orders?: object
    products?: object
    dispatched_emails?: object
    web_users?: object
}>()
let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);



const component = computed(() => {

    const components = {
        showcase: CustomerShowcase,
        products: TableProducts,
        orders: TableOrders,
        details: ModelDetails,
        history: ModelChangelog,
        dispatched_emails: TableDispatchedEmails,
        web_users: TableWebUsers,
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
import TableWebUsers from "@/Components/Tables/Grp/Org/CRM/TableWebUsers.vue";


const isOpen = ref(false);

function setIsOpen(value) {
    isOpen.value = value;
}

const webUserForm = useForm({
    // username: props["customer"].email,
    username: null,
    password: null,
});
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <!--
      Todo: modal forms for quick creation of models
      -->

    <TransitionRoot as="template" :show="isOpen">
        <Dialog
            :open="isOpen"
            @close="setIsOpen"
            as="div"
            class="relative z-10"
        >
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div
                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                >
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm sm:p-6"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-lg font-medium leading-6 text-gray-900"
                                >Create web user</DialogTitle
                            >

                            <div
                                class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6"
                            >
                                <div class="sm:col-span-4">
                                    <label
                                        for="username"
                                        class="block text-sm font-medium text-gray-700"
                                        >Username</label
                                    >
                                    <div class="mt-1">
                                        <input
                                            v-model="webUserForm.username"
                                            id="username"
                                            name="username"
                                            type="text"
                                            autocomplete="email"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <a target="_blank" href="http://pupil.aiku.test/authenticate?shop=aikuu">connecrt shopify</a>
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>
