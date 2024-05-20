<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 20 Jun 2023 20:46:53 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head} from '@inertiajs/vue3'
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import { library } from "@fortawesome/fontawesome-svg-core"
import TableStoredItemReturn from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemReturn.vue"

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
    <TableStoredItemReturn :data="data" app="retina" />
</template>


<style src="@vueform/multiselect/themes/default.css"></style>

<style>
</style>
