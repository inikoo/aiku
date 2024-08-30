<script setup lang='ts'>
import Table from '@/Components/Table/Table.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
    data: any[]
    tab: string
}>()
    
function productRoute(product) {
    // console.log(route().current())
    switch (route().current()) {
        case 'grp.org.shops.show.crm.customers.show.orders.show':
        case 'grp.org.shops.show.ordering.orders.show':
            return route(
                'grp.org.shops.show.catalogue.products.show',
                [route().params['organisation'], route().params['shop'], product.product_slug])
        default:
            return ''
    }
}

</script>


<template>
    <Table :resource="data">
        <template #cell(asset_code)="{ item }">
            <Link :href="productRoute(item)" class="primaryLink">
                {{ item.asset_code }}
            </Link>
        
        </template>
    </Table>
</template>