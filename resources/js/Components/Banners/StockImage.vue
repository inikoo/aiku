<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 04 Oct 2023 08:09:05 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {capitalize} from "@/Composables/capitalize"
import {faSign, faImagePolaroid} from '@fal'
import {library} from "@fortawesome/fontawesome-svg-core";
import Tabs from "@/Components/Navigation/Tabs.vue";
import {computed, ref} from "vue";
import {useTabChange} from "@/Composables/tab-change";
import ModelDetails from "@/Components/ModelDetails.vue";
import TableCustomerHistories from "@/Components/Tables/TableCustomerHistories.vue";
import TableBanners from "@/Components/Tables/TableBanners.vue";

library.add(faSign, faImagePolaroid)

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    changelog?: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {
    const components = {
        details: ModelDetails,
        changelog: TableCustomerHistories,
        banners: TableBanners
    };
    return components[currentTab.value];

});
</script>

<template layout="CustomerApp">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :tab="currentTab" :data="props[currentTab]"></component>
</template>

