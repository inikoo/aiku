<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 03 Oct 2023 15:21:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {ref, computed} from 'vue'
import {Head} from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import TableBannersPortfolioWebsites from "@/Components/Tables/TableBannersPortfolioWebsites.vue"
import {capitalize} from "@/Composables/capitalize"
import {faUpload, faFile as falFile, faTimes} from '@fal'
import {faFile as fasFile, faFileDownload} from '@fas'
import {library} from '@fortawesome/fontawesome-svg-core'
import Tabs from "@/Components/Navigation/Tabs.vue";
import {useTabChange} from "@/Composables/tab-change";
import ModelDetails from "@/Components/ModelDetails.vue";
import TableCustomerHistories from "@/Components/Tables/TableCustomerHistories.vue";

library.add(faUpload, falFile, faTimes, faFileDownload, fasFile)

const props = defineProps<{
    pageHead: any
    title: string
    websites?: object
    changelog?: object
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
        changelog: TableCustomerHistories,
        websites: TableBannersPortfolioWebsites,
    };

    return components[currentTab.value];
});


</script>

<template layout="CustomerApp">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead">
    </PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :tab="currentTab" :data="props[currentTab]"></component>
</template>
