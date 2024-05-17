

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Jan 2024 15:13:18 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup  lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import TablePallets from "@/Components/Tables/Grp/Org/Fulfilment/TablePallets.vue";
import Action from "@/Components/Forms/Fields/Action.vue"
import {useForm} from '@inertiajs/vue3';

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

</script>

<template>
    <Head :title="capitalize(title)"/>
    <TablePallets :data="data" :tab="'pallets'" />
</template>
