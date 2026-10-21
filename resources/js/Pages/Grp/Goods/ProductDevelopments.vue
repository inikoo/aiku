<!--
  - Author Louis Perez
  - Created on 15-10-2026-19h-00m
  - GitHub: https://github.com/louis-perez
  - Copyright 2026
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"
import TableProductDevelopments from "@/Components/Tables/Grp/Goods/TableProductDevelopments.vue"
import ProductDevelopmentViewSwitch from "@/Components/Goods/ProductDevelopment/ProductDevelopmentViewSwitch.vue"
import ProductDevelopmentFilterBar from "@/Components/Goods/ProductDevelopment/ProductDevelopmentFilterBar.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLightbulbOn, faList, faColumns } from "@fal"
library.add(faLightbulbOn, faList, faColumns)

defineProps<{
    data: object
    title: string
    pageHead: PageHeadingTypes
    viewSwitch: any[]
    filterBar: any
}>()
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #otherBefore>
            <ProductDevelopmentViewSwitch :options="viewSwitch" />
        </template>
    </PageHeading>
    <ProductDevelopmentFilterBar :filterBar="filterBar" routeName="grp.product_developments.index" />
    <TableProductDevelopments :data="data" />
</template>
