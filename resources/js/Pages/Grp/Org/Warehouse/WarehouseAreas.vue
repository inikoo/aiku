<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 20:33:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import PageHeading from "@/Components/Headings/PageHeading.vue";
import TableWarehouseAreas from "@/Components/Tables/Grp/Org/Inventory/TableWarehouseAreas.vue";
import { capitalize } from "@/Composables/capitalize";
import Button from "@/Components/Elements/Buttons/Button.vue";
import { get } from 'lodash'
import UploadExcel from "@/Components/Upload/UploadExcel.vue";

const props = defineProps<{
    data: object
    title: string
    pageHead: object
    uploadRoutes: object
}>();

const dataModal = ref({ isModalOpen: false });

const onUploadOpen = (action) => {
    dataModal.value.isModalOpen = true;
    dataModal.value.uploadRoutes = action.route;
};
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-group-upload="{ action : action }">
            <Button
                :style="'upload'"
                @click="()=>onUploadOpen(action.button)"
                class="capitalize inline-flex items-center h-full rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0"
            />
        </template>
    </PageHeading>
    <TableWarehouseAreas :data="data" />

    <UploadExcel
        :propName="'warehouse areas'"
        description="Adding Warehouse Areas"
        :routes="{
            upload: get(dataModal,'uploadRoutes',{})
        }"
        :dataModal="dataModal" />
</template>

