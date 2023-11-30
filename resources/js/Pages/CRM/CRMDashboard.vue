<!--
  - Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
  - Created: Fri, 31 Mar 2023 09:40:16 Central European Summer Time, Malaga, Spain
  - Copyright (c) 2023, Inikoo LTD
  -->



<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import {useLocaleStore} from '@/Stores/locale.js';
import {library} from '@fortawesome/fontawesome-svg-core';
import { capitalize } from "@/Composables/capitalize"
import {
   faShoppingCart
} from '@fal/';
import FlatTreeMap from "@/Components/Navigation/FlatTreeMap.vue";

library.add(
    faShoppingCart
);


const locale = useLocaleStore();


const props = defineProps<{
    title: string,
    pageHead: object,
    flatTreeMaps: object

}>()


</script>


<template layout="App">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead"></PageHeading>
    <FlatTreeMap class="mx-4" v-for="(treeMap,idx) in flatTreeMaps" :key="idx" :nodes="treeMap"/>

</template>
