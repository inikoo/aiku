<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 23:14:33 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
    faCodeCommit,
    faGlobe,
    faGraduationCap,
    faMoneyBill,
    faPaperclip, faPaperPlane, faStickyNote,
    faTags
} from "@/../private/pro-light-svg-icons";
import ModelDetails from "@/Pages/ModelDetails.vue";
import TableOrders from "@/Pages/Tables/TableOrders.vue";
import {useTabChange} from "@/Composables/tab-change";
import {computed, defineAsyncComponent, ref} from "vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableProducts from "@/Pages/Tables/TableProducts.vue";
import TableDispatchedEmails from "@/Pages/Tables/TableDispatchedEmails.vue";

library.add(
    faStickyNote,
    faGlobe,
    faMoneyBill,
    faGraduationCap,
    faTags,
    faCodeCommit,
    faPaperclip,
    faPaperPlane,
)

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    orders?: object
    products?: object
    dispatched_emails?: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        products: TableProducts,
        orders: TableOrders,
        details: ModelDetails,
        history: ModelChangelog,
        dispatched_emails: TableDispatchedEmails,
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


const isOpen = ref(false);

function setIsOpen(value) {
    isOpen.value = value;
}

/*const webUserForm = useForm({
    username: props["customer"].email,
    password: null,
});*/
</script>

<template layout="App">
    <Head :title="title" />
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
    <component :is="component" :data="props[currentTab]"></component>
</template>
