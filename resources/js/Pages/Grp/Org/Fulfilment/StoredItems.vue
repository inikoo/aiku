<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import TableStoredItems from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItems.vue"

import { faNarwhal } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { computed, ref } from 'vue'
import { useTabChange } from '@/Composables/tab-change'
import Tabs from '@/Components/Navigation/Tabs.vue'
import Table from '@/Components/Table/Table.vue'
library.add(faNarwhal)

const props = defineProps<{
    data: {}
    title: string
    pageHead: TSPageHeading
    tabs: {
        current: string;
        navigation: object;
    }
    stored_items : {}
    pallet_stored_items : {}
}>()

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);



const component = computed(() => {

    const components = {
        stored_items:TableStoredItems,
        pallet_stored_items: Table
    };
    return components[currentTab.value];

});

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]" :resource="props[currentTab]" :tab="currentTab" :name="currentTab"></component>
</template>
