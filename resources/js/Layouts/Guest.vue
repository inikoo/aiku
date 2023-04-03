<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Tue, 20 Sept 2022 19:04:29 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { Head } from '@inertiajs/vue3';
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Pages/ModelDetails.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import PageHeading from "@/Components/Headings/PageHeading.vue";

/*
const layout = useLayoutStore();
if (usePage().props.language) {
    loadLanguageAsync(usePage().props.language);
}
watchEffect(() => {

    if (usePage().props.tenant) {
        layout.tenant = usePage().props.tenant ?? null;
    }
});
*/
const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }

}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        details: ModelDetails,
        history: ModelChangelog,
    };
    return components[currentTab.value];

});

</script>

<template>

    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <img class="mx-auto h-16 -mb-3 w-auto" src="/art/logo-color-trimmed.png" alt="Aiku" />
            <h2 class="mt-6 text-center text-3xl text-indigo-600">@{{layout.tenant.code}}</h2>

        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <slot></slot>
            </div>
        </div>
    </div>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>
