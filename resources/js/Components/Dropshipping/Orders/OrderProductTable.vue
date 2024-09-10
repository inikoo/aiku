<script setup lang='ts'>
import Button from '@/Components/Elements/Buttons/Button.vue'
import Table from '@/Components/Table/Table.vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { Link } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { inject, ref } from 'vue'

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

const isLoading = ref<string | boolean>('false')

</script>


<template>
    <Table :resource="data" :name="tab">
        <!-- Column: Code -->
        <template #cell(asset_code)="{ item }">
            <Link :href="productRoute(item)" class="primaryLink">
                {{ item.asset_code }}
            </Link>
        </template>

        <!-- Column: Net -->
        <!-- <template #cell(net_amount)="{ item }">
            {{ locale.currencyFormat(item.currency_code, item.net_amount) }}
        </template> -->

        <!-- Column: Action -->
        <template #cell(actions)="{ item }">
            <Link
                :href="route(item.deleteRoute.name, item.deleteRoute.parameters)"
                :method="item.deleteRoute.method"
                @start="() => isLoading = 'unselect' + item.id"
                @finish="() => isLoading = false"
                v-tooltip="trans('Unselect this product')"
            >
                <Button icon="fal fa-times" type="negative" size="xs" :loading="isLoading === 'unselect' + item.id" />
            </Link>
            <!-- {{ locale.currencyFormat(item.currency_code, item.net_amount) }} -->
        </template>
    </Table>
</template>