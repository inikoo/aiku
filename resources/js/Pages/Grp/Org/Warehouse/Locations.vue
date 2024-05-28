<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 17 Sept 2022 00:32:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import TableLocations from "@/Components/Tables/Grp/Org/Inventory/TableLocations.vue"
import { capitalize } from "@/Composables/capitalize"
import { faWarehouse, faMapSigns } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from "@/Components/Elements/Buttons/Button.vue"
import { get } from "lodash"
import UploadExcel from "@/Components/Upload/UploadExcel.vue"
import { ref } from "vue"
library.add(faWarehouse, faMapSigns)

const props = defineProps<{
    title: string
    pageHead: {}
    data: {}
    tagsList: {
        data: {}
    }
}>()

const dataModal = ref({ isModalOpen: false })

const onUploadOpen = (action) => {
    dataModal.value.isModalOpen = true
    dataModal.value.uploadRoutes = action.route
}
</script>

<template>
    <pre>{{ pageHead }}</pre>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-group-upload="{ action: action }">
            <Button :style="'upload'" @click="() => onUploadOpen(action.button)"
                class="rounded-r-none text-sm border-none focus:ring-transparent focus:ring-offset-transparent focus:ring-0" />
        </template>
    </PageHeading>
    
    <TableLocations :data="data" :tagsList="tagsList.data" />

    <UploadExcel
        :propName="'locations'"
        description="Adding Locations"
        :routes="{
            upload: get(dataModal, 'uploadRoutes', {})
        }"
        :dataModal="dataModal"
    />

</template>
