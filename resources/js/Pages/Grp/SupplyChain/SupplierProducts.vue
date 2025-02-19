<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 13 Sept 2024 15:27:43 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import TableSupplierProducts from "@/Components/Tables/Grp/SupplyChain/TableSupplierProducts.vue";
import { capitalize } from "@/Composables/capitalize"
import { PageHeading as TSPageHeading } from "@/types/PageHeading";
import { ref } from 'vue';
import UploadSpreadsheet from '@/Components/Upload/UploadSpreadsheet.vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import { trans } from 'laravel-vue-i18n'
import UploadExcel from '@/Components/Upload/UploadExcel.vue';

const props = defineProps <{
    pageHead: TSPageHeading
    title: string
    data: object
    importRoutes: object
}>()

const isModalUploadOpen = ref(false)

</script>

<template>
    <Head :title="capitalize(title)"/>
    <PageHeading :data="pageHead">
      <template #other>
          <Button
              @click="() => isModalUploadOpen = true"
              :label="trans('Attach file')"
              icon="fal fa-upload"
              type="secondary"
          />
      </template>
    </PageHeading>
    <TableSupplierProducts :data="data" />

    <UploadExcel
        v-model="isModalUploadOpen"
        scope="Pallet delivery"
        :title="{
            label: 'Upload your new pallet deliveries',
            information: 'The list of column file: customer_reference, notes, stored_items'
        }"
        progressDescription="Adding Pallet Deliveries"        
        :upload_spreadsheet="importRoutes"
        
    />
    
</template>

