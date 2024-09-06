<script setup lang='ts'>
import Table from '@/Components/Table/Table.vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { Link } from '@inertiajs/vue3'
import { inject } from 'vue'

const props = defineProps<{
    data: any[]
    tab: string
}>()
    
function productRoute(product) {
    // console.log(route().current())
    switch (route().current()) {
        case 'grp.org.shops.show.crm.customers.show.orders.show':
        case 'grp.org.shops.show.ordering.orders.show':
            if(product.product_slug) {
                return route(
                    'grp.org.shops.show.catalogue.products.show',
                    [route().params['organisation'], route().params['shop'], product.product_slug])
            }
            return ''
        default:
            return ''
    }
}

const locale = inject('locale', aikuLocaleStructure)

</script>


<template>
    <Table :resource="data">
        <!-- Column: Code -->
        <template #cell(asset_code)="{ item }">
            <Link :href="productRoute(item)" class="primaryLink">
                {{ item.asset_code }}
            </Link>
        </template>

        <!-- Column: Net -->
        <template #cell(net_amount)="{ item }">
            {{ locale.currencyFormat(item.currency_code, item.net_amount) }}
        </template>
    </Table>
</template>