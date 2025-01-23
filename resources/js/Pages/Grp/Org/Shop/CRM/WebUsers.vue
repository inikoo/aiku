<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 15 Feb 2024 02:24:05 Malaysia Time, Madrid Spain
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableWebUsers from "@/Components/Tables/Grp/Org/CRM/TableWebUsers.vue";
import TableWebUserRequests from "@/Components/Tables/Grp/Org/CRM/TableWebUserRequests.vue";
import { capitalize } from "@/Composables/capitalize"
import { library } from '@fortawesome/fontawesome-svg-core'
import Table from '@/Components/Table/Table.vue'
import { useTabChange } from '@/Composables/tab-change'
import Tabs from '@/Components/Navigation/Tabs.vue'
import { computed, ref } from 'vue'
import { faTerminal } from '@fal'
import { faClock } from '@far'
library.add(faTerminal, faClock)


const props = defineProps <{
    pageHead: object
    title: string
    data:object
    tabs: {
        current: string;
        navigation: object;
    }
    web_users: object
    requests: object
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);



const component = computed(() => {

    const components = {
        web_users: TableWebUsers,
        requests: TableWebUserRequests,
    };
    return components[currentTab.value];

});
console.log(currentTab.value)

</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]" :resource="props[currentTab]" :tab="currentTab" :name="currentTab"></component>
</template>

