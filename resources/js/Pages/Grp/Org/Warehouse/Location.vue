<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 17 Sept 2022 02:06:31 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import type { Component } from "vue";
import { Head } from "@inertiajs/vue3";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faBox, faExchange, faInventory, faWarehouse, faMapSigns ,faPallet} from "@fal";
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Components/ModelDetails.vue";
import TableHistories from "@/Components/Tables/TableHistories.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import { capitalize } from "@/Composables/capitalize";
import LocationShowcase from "@/Components/Showcases/Org/LocationShowcase.vue";

library.add(faInventory, faExchange, faBox, faWarehouse, faMapSigns,faPallet);

const ModelChangelog = defineAsyncComponent(() => import("@/Components/ModelChangelog.vue"));

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    },
    details?: object,
    history?: object,
    stocks?: object,
    pallets?:object

}>();

let currentTab = ref(props.tabs.current || route().v().query.tab || "showcase");
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {
    const components: Component = {
        showcase: LocationShowcase,
        details: ModelDetails,
        history: TableHistories
    };

    return components[currentTab.value];
});

</script>

<!--suppress HtmlUnknownAttribute -->
<template layout="App">
    <!--suppress HtmlRequiredTitleElement -->
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>

