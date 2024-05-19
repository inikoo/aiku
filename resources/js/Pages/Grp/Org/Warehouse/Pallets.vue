<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 19:25:03 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup  lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import { capitalize } from "@/Composables/capitalize"
import TablePallets from "@/Components/Tables/Grp/Org/Inventory/TablePallets.vue";
import Action from "@/Components/Forms/Fields/Action.vue"
import {useForm} from '@inertiajs/vue3';
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
faStickyNote,

} from '@fal';
library.add(
  faStickyNote,
)

defineProps<{
    data: object
    title: string
    pageHead: PageHeadingTypes
}>()

const form = useForm({ pallet : []})

</script>

<template>
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
