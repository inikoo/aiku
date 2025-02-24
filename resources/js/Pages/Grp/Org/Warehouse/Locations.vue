<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sat, 17 Sept 2022 00:32:56 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import TableLocations from "@/Components/Tables/Grp/Org/Inventory/TableLocations.vue"
import { capitalize } from "@/Composables/capitalize"
import { faWarehouse, faMapSigns } from '@fal'
import { faFileExport } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from "@/Components/Elements/Buttons/Button.vue"
import { get } from "lodash"
import UploadExcel from "@/Components/Upload/UploadExcel.vue"
import { ref } from "vue"
import { trans } from 'laravel-vue-i18n'
import { routeType } from '@/types/route'
library.add(faWarehouse, faMapSigns, faFileExport)

const props = defineProps<{
    title: string
    pageHead: {}
    data: {}
    tagsList: {
        data: {}
    }
    export: {
        route: routeType
        columns: {
            lable: string
            value: string
        }[]
    }
}>()

const isModalUploadOpen = ref(false)

const isExporting = ref(false)
const progressExport = ref(0)
const onExport = () => {
    router[props.export.method || 'post'](
        route(props.export.route.name, props.export.route.parameters),
        {
            columns: ['status']
        }, {
            onStart: () => {
                isExporting.value = true
            },
            onProgress: (progress: number) => {
                progressExport.value = progress
            },
            onFinish: () => {
                isExporting.value = false
                progressExport.value = 0
            },
        }

    )
}
</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- <template #otherBefore>
            <Button
                @click="() => onExport()"
                type="tertiary"
                icon="fas fa-file-export"
                :loading="isExporting"
            >
                <template #label>
                    <div>
                        {{ trans('Export') }}
                        <span v-if="isExporting">
                            {{ progressExport }}/100%
                        </span>
                    </div>
                </template>
            </Button>
        </template> -->

        <template #button-group-upload="{ action }">
            <Button
                @click="() => isModalUploadOpen = true"
                :style="'upload'"
                class="rounded-r-none text-sm border-none focus:ring-transparent focus:ring-offset-transparent focus:ring-0"
            />
        </template>
    </PageHeading>

    <!-- <pre>{{ export }}</pre> -->
    <TableLocations :data="data" :tagsList="tagsList.data" />

    <UploadExcel
        v-model="isModalUploadOpen"
        scope="Pallet delivery"
        :title="{
            label: 'Upload your new pallet deliveries',
            information: 'The list of column file: customer_reference, notes, stored_items'
        }"
        progressDescription="Adding Pallet Deliveries"
    />

</template>
