<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 08 Sept 2022 00:38:38 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {library} from '@fortawesome/fontawesome-svg-core';
import { faEnvelope, faIdCard, faPhone, faSignature, faUser, faBirthdayCake, faVenusMars, faHashtag, faHeading, faHospitalUser, faClock, faPaperclip, faTimes, faCameraRetro} from "@/../private/pro-light-svg-icons";
import {faCheckCircle} from '@/../private/pro-solid-svg-icons';

import PageHeading from '@/Components/Headings/PageHeading.vue';

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
    faCameraRetro
)
import { computed, defineAsyncComponent, ref } from "vue";
import { useTabChange } from "@/Composables/tab-change";
import ModelDetails from "@/Pages/ModelDetails.vue";
import Tabs from "@/Components/Navigation/Tabs.vue";


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


<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate"/>
    <component :is="component" :data="props[currentTab]"></component>
</template>

