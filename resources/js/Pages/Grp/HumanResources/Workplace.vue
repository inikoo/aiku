<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 08 Sept 2022 00:38:38 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faEnvelope, faIdCard, faPhone, faSignature, faUser, faBuilding, faBirthdayCake, faVenusMars, faHashtag, faHeading, faHospitalUser, faClock, faPaperclip, faTimes, faCameraRetro, faChessClock } from "@fal";
import { faCheckCircle } from "@fas";
import { capitalize } from "@/Composables/capitalize";
import TableHistories from "@/Components/Tables/TableHistories.vue";

import PageHeading from "@/Components/Headings/PageHeading.vue";

library.add(
    faIdCard,
    faUser,
    faCheckCircle,
    faSignature,
    faEnvelope,
    faPhone,
    faIdCard,
    faBirthdayCake,
    faVenusMars,
    faHashtag,
    faHeading,
    faHospitalUser,
    faClock,
    faPaperclip,
    faTimes,
    faCameraRetro,
    faBuilding,
    faChessClock,

);
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Components/ModelDetails.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";
import TableClockingMachine from "@/Components/Tables/TableClockingMachines.vue";
import TableClockings from "@/Components/Tables/TableClockings.vue";


const ModelChangelog = defineAsyncComponent(() => import("@/Components/ModelChangelog.vue"));

const props = defineProps<{
    title: string,
    pageHead: object,
    tabs: {
        current: string;
        navigation: object;
    }
    clocking_machines?: object;
    clockings?: object;
    history?: object;

}>();

let currentTab = ref(props.tabs.current);
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab);

const component = computed(() => {

    const components = {
        clocking_machines: TableClockingMachine,
        clockings: TableClockings,
        details: ModelDetails,
        history: TableHistories
    };
    return components[currentTab.value];

});


</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab"></component>
</template>

