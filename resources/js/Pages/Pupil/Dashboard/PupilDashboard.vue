<script setup lang='ts'>
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import SelectButton from 'primevue/selectbutton'
import DataView from 'primevue/dataview'
import IconField from 'primevue/iconfield'
import Rating from 'primevue/rating'
import { FilterMatchMode } from '@primevue/core/api'
import { onMounted, ref } from 'vue'
import { useLocaleStore } from '@/Stores/locale'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { routeType } from '@/types/route'
import axios from 'axios'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faThLarge, faListUl } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { router } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
library.add(faSearch, faThLarge, faListUl)

const props = defineProps<{
    shop: string
    routes: {
        products: routeType 
        store_product: routeType
    }
    // token: string
    token_request: string
}>()

// console.log('token', Object.keys(props.token)[1])
// const xxToken = Object.keys(props.token)[1].match(/login_pupil_([a-f0-9]+)/)?.[1]
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
    // const token_token = props.token['_token']
    // const tokenLoginPupil = Object.keys(props.token).filter(tok => tok.includes('login_pupil'))?.[0]?.match(/login_pupil_([a-f0-9]+)/)?.[1]
    
    // console.log('token:', props.token)
    // console.log('props token (CSRF):', token_token)
    // console.log('token login pupil:', tokenLoginPupil)
    
    // Get window.sessionToken
    // try {
    //     const dataxx = await axios.get('authenticate/token')
    //     console.log('============= success hit authenticate/token', dataxx)

    // } catch (error) {
    //     console.error('-------------------', error)
    // }

    try {
        const { data } = await axios.get(route(props.routes.products.name, props.routes.products.parameters),
            {
                headers: {
                    Authorization: `Bearer ${props.token_request}`,
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }
        )

        realProducts.value = data.data.slice(0, 5)
        console.log('aaa', realProducts.value)
    } catch (error) {
        console.log('error', error)
    }

})


// Selected product
const isLoadingSubmit = ref(false)
const selectedProducts = ref([])
const isSelected = (id) => {
    return selectedProducts.value.some(item => item.id === id);
};
const onSubmitProduct = () => {
    isLoadingSubmit.value = true
    router.post(
        route(props.routes.store_product.name, props.routes.store_product.parameters),
        {
            products: selectedProducts.value.map(sel => sel.id)
        },
        {
            headers: {
                Authorization: `Bearer ${props.token_request}`,
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        }
    )
    isLoadingSubmit.value = false
}


const productView = ref('list')
const optionsView = [
    {
        id: 1,
        label: trans('Grid'),
        value: 'grid',
        icon: 'fal fa-th-large'
    },
    {
        id: 2,
        label: trans('List'),
        value: 'list',
        icon: 'fal fa-list-ul'
    }
]

import { faStar } from '@fas'
import Checkbox from '@/Components/Checkbox.vue'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import Select from 'primevue/select'
library.add(faStar)

const toggleItem = (id) => {
    const index = selectedProducts.value.findIndex(item => item.id === id);
    if (index !== -1) {
        // If item is found, remove it
        selectedProducts.value.splice(index, 1)
    } else {
        // If item is not found, add it
        selectedProducts.value.push({id: id})
    }
}

const isLoadingDisplay = ref(false)
const onChangeDisplay = (type: string) => {
    if (productView.value == type) return
    productView.value = type
}

// View: Grid
const sortKey = ref()
const sortOrder = ref()
const sortField = ref()
const gridSortOptions = ref([
    {label: 'Price High to Low', value: '!price'},
    {label: 'Price Low to High', value: 'price'},
])
const onSortChange = (event) => {
    const value = event.value.value;
    const sortValue = event.value;

    if (value.indexOf('!') === 0) {
        sortOrder.value = -1;
        sortField.value = value.substring(1, value.length);
        sortKey.value = sortValue;
    }
    else {
        sortOrder.value = 1;
        sortField.value = value;
        sortKey.value = sortValue;
    }
}
</script>

<template>
    <div class="p-8">
    <!-- <pre>{{ props }}</pre> -->
        <!-- <h1>Hello from Vue Dashboard!</h1> -->
        <h4 class="font-bold text-2xl mb-3">Here you can add our Aw-Dropship products automatically to your shop 😲</h4>

        <!-- Select: Grid and List -->
        <div class="flex justify-end gap-x-3 mb-2">
            <SelectButton :modelValue="productView" @update:modelValue="(e: string) => onChangeDisplay(e)" :allowEmpty="false" :options="optionsView" optionValue="value" dataKey="value" aria-labelledby="custom">
                <template #option="{ option }">
                    <FontAwesomeIcon :icon='option.icon' class='' fixed-width aria-hidden='true' />
                </template>
            </SelectButton>

            <Button @click="() => onSubmitProduct()" :loading="isLoadingSubmit" label="Submit" :disabled="!selectedProducts.length" type="black" />
        </div>

        <div class="bg-stone-100 overflow-hidden rounded-2xl border border-stone-300">
            <DataTable v-if="productView === 'list'" ref="_dt"
                v-model:selection="selectedProducts"
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
                <Column field="`sta`te" header="State" style="min-width: 8rem">
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

            <!-- View: Grid -->
            <DataView v-else :value="realProducts" paginator :rows="12">
                <template #header>
                    <Select v-model="sortKey" :options="gridSortOptions" optionLabel="label" placeholder="Sort By Price" @change="onSortChange($event)" />
                </template>

                <template #list="{ items }">
                    <div class="p-4 grid grid-cols-12 gap-4">
                        <div v-for="(item, index) in items" :key="index"
                            @click="() => toggleItem(item.id)"
                            class="cursor-pointer h-full border rounded-lg flex flex-col col-span-12 sm:col-span-6 lg:col-span-3"
                            :class="[isSelected(item.id) ? 'bg-stone-200 ring-1 ring-stone-500' : 'hover:bg-stone-100']"
                        >
                        <!-- == {{ isSelected(item.id) }} == -->
                            <div class="relative flex justify-center rounded">
                                <img class="rounded w-full" :src="`https://primefaces.org/cdn/primevue/images/product/gaming-set.jpg`" :alt="item.name" style="max-width: 300px"/>
                                <div class="absolute top-1.5 left-2">
                                    <div class="capitalize text-xs inline-flex items-center gap-x-1 rounded select-none px-1.5 py-0.5 w-fit font-medium bg-emerald-100 hover:bg-emerald-200 border border-emerald-200 text-emerald-500" :theme="13">
                                        {{ item.state }}
                                    </div>
                                </div>
                                <div class="absolute top-1.5 right-2">
                                    <input :checked="isSelected(item.id)" name="checkboxProduct" type="checkbox" class="cursor-pointer h-5 w-5  rounded border-stone-300 text-stone-800 shadow-sm focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                            <div class="py-4 px-6 h-full flex flex-col justify-between">
                                <div class="flex flex-row justify-between items-start gap-2">
                                    <div>
                                        <span class="text-stone-500 text-sm">{{ item.code }}</span>
                                        <div class="text-lg font-medium">{{ item.name }}</div>
                                    </div>
                                </div>
                    
                                <!-- Section: Price -->
                                <div class="flex justify-between mt-6">
                                    <span class="text-2xl font-semibold">${{ item.price }}</span>
                    
                                    <div class="p-1" style="border-radius: 30px">
                                        <div class="flex items-center gap-2 justify-center py-1 px-2" style="border-radius: 30px; box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.04), 0px 1px 2px 0px rgba(0, 0, 0, 0.06)">
                                            <span class="font-medium text-sm">{{ item.rating || 0 }}</span>
                                            <!-- <i class="pi pi-star-fill "></i> -->
                                            <FontAwesomeIcon icon='fas fa-star' class='text-yellow-500' fixed-width aria-hidden='true' />
                                        </div>
                                    </div>
                                    <!-- <div class="flex gap-2">
                                        <Button icon="pi pi-shopping-cart" label="Buy Now" :disabled="item.inventoryStatus === 'OUTOFSTOCK'" class="flex-auto whitespace-nowrap"></Button>
                                        <Button icon="pi pi-heart" outlined></Button>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </DataView>
        </div>

        
    </div>
</template>

<style scoped lang="scss">
:root {
    --primary-color: #ff0000
}

</style>