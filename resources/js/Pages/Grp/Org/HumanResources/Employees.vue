<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 16 May 2024 12:23:41 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import TableEmployees from "@/Components/Tables/Grp/Org/HumanResources/TableEmployees.vue"
import { capitalize } from "@/Composables/capitalize"
import "@/Composables/Icon/EmployeeStateIcon.ts"
import { ref } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'

const props = defineProps<{
    pageHead: {}
    title: string
    data: {}
    upload_spreadsheet: {}
}>()

const isModalUploadOpen = ref(false)

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-btn-upload="{ action }">
            <Button
                @click="() => isModalUploadOpen = true"
                :style="action.style"
                :icon="action.icon"
                v-tooltip="action.tooltip"
            />
        </template>
    </PageHeading>
    <TableEmployees :data="data" />

    <UploadExcel
        v-model="isModalUploadOpen"
        scope="Pallet delivery"
        :title="{
            label: 'Upload your new pallet deliveries',
            information: 'The list of column file: customer_reference, notes, stored_items'
        }"
        progressDescription="Adding Pallet Deliveries"        
        :upload_spreadsheet
    />
</template>

