<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 20:46:53 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import TableStoredItemReturn from "@/Components/Tables/TableStoredItemReturn.vue"
import TablePalletReturns from "@/Components/Tables/TablePalletReturns.vue"
import Table from "@/Components/Table/Table.vue"
library.add()


const props = defineProps<{
    title: string,
    pageHead: {},
    tabs: {
        current: string
        navigation: {}
    }
    data: {}
}>()


console.log(props)


</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead" />
    <Table :resource="data" name="stored_item_returns">
    </Table>
<!--     <TablePallets :data="data"  tab="stored_item_returns" /> -->
</template>


<style src="@vueform/multiselect/themes/default.css"></style>

<style>
</style>
