<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import { faArrowAltToTop, faArrowAltToBottom, faBars,faBrowser,faCube,faPalette,faCookieBite } from '@fal';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { computed, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import Tabs from "@/Components/Navigation/Tabs.vue";
import WorkshopHeader from "@/Components/CMS/Workshops/HeaderWorkshop.vue";
import WorkshopMenu from "@/Components/CMS/Workshops/MenuWorkshop.vue";
import WorkshopFooter from "@/Components/CMS/Workshops/Footer/FooterWorkshop.vue";
import ColorSchemeWorkshop from "@/Components/CMS/Workshops/ColorSchemeWorkshop.vue";
import { capitalize } from "@/Composables/capitalize"

library.add(
    faArrowAltToTop,
    faArrowAltToBottom,
    faBars,
    faBrowser,
    faCube,
    faPalette,
    faCookieBite
);

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    color_scheme?: object;
    header?: object;
    menu?: object;
    footer?: object;
    category?: object;
    product?: object;
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        color_scheme: ColorSchemeWorkshop,
        header: WorkshopHeader,
        menu: WorkshopMenu,
        footer: WorkshopFooter,
    };
    return components[currentTab.value];

});

</script>


<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>

