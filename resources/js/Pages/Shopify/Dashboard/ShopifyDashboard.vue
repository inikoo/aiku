<script setup lang='ts'>
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Row from 'primevue/row'
import ColumnGroup from 'primevue/columngroup'
import Tag from 'primevue/tag'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import Rating from 'primevue/rating'
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref } from 'vue'
import { useLocaleStore } from '@/Stores/locale'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { routeType } from '@/types/route'
import axios from 'axios'

const props = defineProps<{
    productsRoute: routeType 
}>()

const locale = useLocaleStore()

const selectedProducts = ref(null)
const productDialog = ref(false)
const deleteProductDialog = ref(false)
const filters = {
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
}

const dummyProduct = [
    {
        id: '1000',
        code: 'f230fh0g3',
        name: 'Bamboo Watch',
        description: 'Product Description',
        image: 'bamboo-watch.jpg',
        price: 65,
        category: 'Accessories',
        quantity: 24,
        inventoryStatus: 'INSTOCK',
        rating: 5
    },
    {
        id: '1001',
        code: 'nvklal433',
        name: 'Black Watch',
        description: 'Product Description',
        image: 'black-watch.jpg',
        price: 72,
        category: 'Accessories',
        quantity: 61,
        inventoryStatus: 'INSTOCK',
        rating: 4
    },
    {
        id: '1002',
        code: 'zz21cz3c1',
        name: 'Blue Band',
        description: 'Product Description',
        image: 'blue-band.jpg',
        price: 79,
        category: 'Fitness',
        quantity: 2,
        inventoryStatus: 'LOWSTOCK',
        rating: 3
    },
    {
        id: '1003',
        code: '244wgerg2',
        name: 'Blue T-Shirt',
        description: 'Product Description',
        image: 'blue-t-shirt.jpg',
        price: 29,
        category: 'Clothing',
        quantity: 25,
        inventoryStatus: 'INSTOCK',
        rating: 5
    },
    {
        id: '1004',
        code: 'h456wer53',
        name: 'Bracelet',
        description: 'Product Description',
        image: 'bracelet.jpg',
        price: 15,
        category: 'Accessories',
        quantity: 73,
        inventoryStatus: 'INSTOCK',
        rating: 4
    },
    {
        id: '1005',
        code: 'av2231fwg',
        name: 'Brown Purse',
        description: 'Product Description',
        image: 'brown-purse.jpg',
        price: 120,
        category: 'Accessories',
        quantity: 0,
        inventoryStatus: 'OUTOFSTOCK',
        rating: 4
    },
    {
        id: '1006',
        code: 'bib36pfvm',
        name: 'Chakra Bracelet',
        description: 'Product Description',
        image: 'chakra-bracelet.jpg',
        price: 32,
        category: 'Accessories',
        quantity: 5,
        inventoryStatus: 'LOWSTOCK',
        rating: 3
    },
    {
        id: '1007',
        code: 'mbvjkgip5',
        name: 'Galaxy Earrings',
        description: 'Product Description',
        image: 'galaxy-earrings.jpg',
        price: 34,
        category: 'Accessories',
        quantity: 23,
        inventoryStatus: 'INSTOCK',
        rating: 5
    },
    {
        id: '1008',
        code: 'vbb124btr',
        name: 'Game Controller',
        description: 'Product Description',
        image: 'game-controller.jpg',
        price: 99,
        category: 'Electronics',
        quantity: 2,
        inventoryStatus: 'LOWSTOCK',
        rating: 4
    },
    {
        id: '1009',
        code: 'cm230f032',
        name: 'Gaming Set',
        description: 'Product Description',
        image: 'gaming-set.jpg',
        price: 299,
        category: 'Electronics',
        quantity: 63,
        inventoryStatus: 'INSTOCK',
        rating: 3
    },
    {
        id: '1010',
        code: 'plb34234v',
        name: 'Gold Phone Case',
        description: 'Product Description',
        image: 'gold-phone-case.jpg',
        price: 24,
        category: 'Accessories',
        quantity: 0,
        inventoryStatus: 'OUTOFSTOCK',
        rating: 4
    },
]

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

const realProducts = ref([])
onMounted(async () => {
    try {
        const {data} = await axios.get(route(props.productsRoute.name, props.productsRoute.parameters))
        realProducts.value = data.data
        console.log('aaa', realProducts.value)
    } catch (error) {
        console.log('error', error)
    }
})
</script>

<template>
    <div class="p-8">
        <h1>Hello from Vue Dashboard!</h1>

        <div class="overflow-hidden rounded-2xl border border-gray-300">
            <!-- <DataTable :value="products" stripedRows showGridlines removableSort tableStyle="min-width: 50rem">
                <Column field="image" class="overflow-hidden transition-all w-32" header="Image">
                    <template #body="{ data }">
                        <div class="relative flex justify-center">
                            <img :src="data.imageSrc" class="h-24 w-auto" />
                        </div>
                    </template>
                </Column>

                <Column field="name" sortable class="overflow-hidden transition-all" header="Name"
                    headerStyle="text-align: green; width: 250px" headerClass="bg-red-500">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.name">
                                    {{ data.name }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>
                <Column field="price" sortable class="overflow-hidden transition-all" header="Price"
                    headerStyle="text-align: green; width: 250px" headerClass="bg-red-500">
                    <template #body="{ data }">
                        <div class="flex justify-end relative">
                            <Transition name="spin-to-down" mode="out-in">
                                <div :key="data.price">
                                    {{ data.price }}
                                </div>
                            </Transition>
                        </div>
                    </template>
                </Column>
            </DataTable> -->

            <DataTable ref="dt" v-model:selection="selectedProducts" :value="realProducts" dataKey="id" :paginator="true"
                :rows="20" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 20]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} products">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Products</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>

                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>

                <Column field="code" header="Code" sortable style="min-width: 12rem">
                
                </Column>

                <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>

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
        </div>
    </div>
</template>