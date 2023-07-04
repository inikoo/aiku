

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faArrowAltToTop, faArrowAltToBottom, faBars,faBrowser
} from "@/../private/pro-light-svg-icons";

import PageHeading from '@/Components/Headings/PageHeading.vue';
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import Tabs from "@/Components/Navigation/Tabs.vue";
import IrisWorkshopHeader from "@/Pages/Iris/IrisWorkshopHeader.vue";
import IrisWorkshopMenu from "@/Pages/Iris/IrisWorkshopMenu.vue";
import IrisWorkshopFooter from "@/Pages/Iris/IrisWorkshopFooter.vue";
import { capitalize } from "@/Composables/capitalize"

library.add(
    faArrowAltToTop,
    faArrowAltToBottom,
    faBars,
    faBrowser
);

const ModelChangelog = defineAsyncComponent(() => import('@/Pages/ModelChangelog.vue'))

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    header?: object;
    menu?: object;
    footer?: object;
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        header: IrisWorkshopHeader,
        menu: IrisWorkshopMenu,
        footer: IrisWorkshopFooter,
    };
    return components[currentTab.value];

});

</script>


<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>

