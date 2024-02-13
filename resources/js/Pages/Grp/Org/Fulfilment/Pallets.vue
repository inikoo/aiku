

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sat, 27 Jan 2024 15:13:18 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup  lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import TablePallets from "@/Components/Tables/TablePallets.vue";
import Action from "@/Components/Forms/Fields/Action.vue"
import {useForm} from '@inertiajs/vue3';

const props = defineProps<{
    data: object
    title: string
    pageHead: object
}>()

const form = useForm({ pallet : []})

</script>

<template layout="App">
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead">
    <template #button-new-delivery="{ action: action }">
    <div v-if="form.pallet.length > 0">
      <Action v-if="action.action" :action="action.action"/>
    </div>
    <div v-else></div>
      
    </template>
    </PageHeading>
    <TablePallets :data="data" :tab="'pallets'" :form="form" :dataToSubmit="form.data()" />
</template>
