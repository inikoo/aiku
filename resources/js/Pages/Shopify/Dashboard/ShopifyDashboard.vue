<script setup lang='ts'>
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Row from 'primevue/row'
import ColumnGroup from 'primevue/columngroup'
import Tag from 'primevue/tag'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import ButtonPV from 'primevue/button'
import Rating from 'primevue/rating'
import { FilterMatchMode } from '@primevue/core/api'
import { onMounted, ref } from 'vue'
import { useLocaleStore } from '@/Stores/locale'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { routeType } from '@/types/route'
import axios from 'axios'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { router } from '@inertiajs/vue3'
library.add(faSearch)

const props = defineProps<{
    routes: {
        products: routeType 
        store_product: routeType
    }
}>()

const locale = useLocaleStore()

const productDialog = ref(false)
const deleteProductDialog = ref(false)
const filters = {
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
}

const confirmDeleteProduct = (product) => {
    product.value = product
    deleteProductDialog.value = true
}

const editProduct = (product) => {
    product.value = {...product}
    productDialog.value = true
}

const getStatusLabel = (status) => {
    switch (status) {
        case 'INSTOCK':
            return 'success';

        case 'LOWSTOCK':
            return 'warn';

        case 'OUTOFSTOCK':
            return 'danger';

        default:
            return null;
    }
}

// Fetch: product
const realProducts = ref([])
onMounted(async () => {
    try {
        const {data} = await axios.get(route(props.routes.products.name, props.routes.products.parameters))
        realProducts.value = data.data
        console.log('aaa', realProducts.value)
    } catch (error) {
        console.log('error', error)
    }
})


// Selected product
const isLoadingSubmit = ref(false)
const selectedProducts = ref([])
const onSubmitProduct = () => {
    router.post(
        route(props.routes.store_product.name, props.routes.store_product.parameters),
        selectedProducts.value,
        {
            onStart: () => isLoadingSubmit.value = true,
            onFinish: () => isLoadingSubmit.value = false
        }
    )
}

</script>

<template>
    <div class="p-8">
        <!-- <h1>Hello from Vue Dashboard!</h1> -->
        <h4 class="font-bold text-2xl mb-3">Here you can add our Aw-Dropship products automatically to your shop 😲</h4>
        <div class="bg-gray-100 overflow-hidden rounded-2xl border border-gray-300">

            <DataTable ref="dt"
                v-model:selection="selectedProducts"
                @update:selection="(e) => console.log(e)"
                :value="realProducts"
                dataKey="id"
                :paginator="true"
                :rows="20"
                :filters="filters"
                scrollable 
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 20, 40]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products">
                <template #header headerStyle="background: #ff0000">
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <IconField>
                            <InputIcon>
                                <FontAwesomeIcon icon='fal fa-search' class='' fixed-width aria-hidden='true' />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>

                        <Button @click="() => onSubmitProduct()" :loading="isLoadingSubmit" label="Submit" :disabled="!selectedProducts.length" type="black">
                            
                        </Button>
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false" frozen ></Column>

                <Column field="name" header="Name" sortable style="min-width: 16rem" frozen ></Column>

                <Column field="code" header="Code" sortable style="min-width: 12rem">
                
                </Column>

                <Column header="Image">
                    <template #body="slotProps">
                        <img :src="`https://primefaces.org/cdn/primevue/images/product/bracelet.jpg`"
                            :alt="slotProps.data.image" class="rounded" style="width: 64px" />
                    </template>
                </Column>

                <Column field="price" header="Price" sortable style="min-width: 8rem">
                    <template #body="{ data }">
                        {{ locale.currencyFormat('usd', data.price) }}
                    </template>
                </Column>

                <!-- <Column field="category" header="Category" sortable style="min-width: 10rem"></Column> -->

                <Column field="rating" header="Reviews" sortable style="min-width: 12rem">
                    <template #body="slotProps">
                        <Rating :modelValue="slotProps.data.rating" :readonly="true" />
                    </template>
                </Column>

                <Column field="state" header="State" style="min-width: 8rem">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.state"
                            :severity="getStatusLabel(slotProps.data.inventoryStatus)" />
                    </template>
                </Column>

                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <div class="flex gap-x-1">
                            <Button type="edit" class="mr-2" @click="editProduct(slotProps.data)" />
                            <Button type="delete" outlined rounded severity="danger" @click="confirmDeleteProduct(slotProps.data)" />
                        </div>
                    </template>
                </Column>

            </DataTable>
            {{ selectedProducts }}
        </div>
    </div>
</template>

<style scoped lang="scss">
:root {
    --primary-color: #ff0000
}

</style>