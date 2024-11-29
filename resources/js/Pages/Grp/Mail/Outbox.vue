<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import { computed, ref } from 'vue'
import type { Component } from 'vue'

import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import { PageHeading as TSPageHeading } from '@/types/PageHeading'
import { Tabs as TSTabs } from '@/types/Tabs'
import Beetree from '@/Components/CMS/Website/Outboxes/Beetree.vue'
import Unlayer from "@/Components/CMS/Website/Outboxes/Unlayer/UnlayerV2.vue"

/* import { BlockManager, BasicType, AdvancedType } from 'easy-email-core';
import { EmailEditor, EmailEditorProvider } from 'easy-email-editor';
import 'easy-email-editor/lib/style.css'; */

import { library } from "@fortawesome/fontawesome-svg-core";
import { faInboxOut }  from "@fal";
library.add(faInboxOut)

const props = defineProps<{
    title: string,
    pageHead: TSPageHeading
    tabs: TSTabs
    history?: {}

    imagesUploadRoute: routeType
    updateRoute: routeType
    emailTemplate: routeType
    publishRoute: routeType
    loadRoute: routeType
    
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

const component = computed(() => {

    const components: Component = {
        // showcase: FileShowcase
        history: TableHistories,
    }

    return components[currentTab.value]

})


</script>


<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Tabs :current="currentTab" :navigation="tabs.navigation" @update:tab="handleTabUpdate" />

    <component :is="component" :data="props[currentTab as keyof typeof props]" :tab="currentTab" />

   <!--  <Beetree :updateRoute="updateRoute" :loadRoute="loadRoute" :imagesUploadRoute="imagesUploadRoute" :mailshot="{}" /> -->
    <!-- <Unlayer :updateRoute="updateRoute" :loadRoute="loadRoute" :imagesUploadRoute="imagesUploadRoute" :mailshot="{}" /> -->



</template>