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
import { library } from "@fortawesome/fontawesome-svg-core";
// import Button from "@/Components/Elements/Buttons/Button.vue";
// import { get } from 'lodash'
// import UploadExcel from "@/Components/Upload/UploadExcel.vue";
import {
    faIndustryAlt
} from "@fal";
library.add(faIndustryAlt);

const props = defineProps<{
    data: {}
    title: string
    pageHead: {}
    uploadRoutes: {}
}>();

const dataModal = ref({ isModalOpen: false });

// const onUploadOpen = (action) => {
//     dataModal.value.isModalOpen = true;
//     dataModal.value.uploadRoutes = action.route;
// }

// const isModalUploadOpen = ref(false)
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- <template #button-group-upload="{ action }">
            <Button
                :style="'upload'"
                @click="()=>onUploadOpen(action)"
                class="capitalize inline-flex items-center h-full rounded-none text-sm border-none font-medium shadow-sm focus:ring-transparent focus:ring-offset-transparent focus:ring-0"
            />
        </template> -->
    </PageHeading>
    <TableWarehouseAreas :data="data" />

    
    <!-- <UploadExcel
        v-model="isModalUploadOpen"
        scope="Warehouse areas"
        :title="{
            label: 'Upload your new warehouse areas',
            information: 'The list of column file: -'
        }"
        progressDescription="Adding Pallet Deliveries"        
        :upload_spreadsheet
    /> -->
</template>

