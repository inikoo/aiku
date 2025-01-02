<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TablePurchaseOrders from "@/Components/Tables/Grp/Org/Procurement/TablePurchaseOrders.vue";
import { capitalize } from "@/Composables/capitalize"

import { library } from "@fortawesome/fontawesome-svg-core"
import { inject, ref, computed, onMounted, onUnmounted } from "vue"
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"
import { faGhost, faSeedling, faShare, faSignOutAlt, faSpellCheck } from "@fal"

const locale = inject("locale", aikuLocaleStructure)

library.add(
	faShare,
    faSeedling,
    faSpellCheck,
    faSignOutAlt,
    faGhost
)

const props = defineProps <{
    pageHead: object
    title: string
    data:object
}>()
</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead"></PageHeading>
    <TablePurchaseOrders :data="data" />
</template>

