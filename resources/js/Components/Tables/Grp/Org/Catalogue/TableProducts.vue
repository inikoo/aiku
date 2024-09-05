<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { Product } from "@/types/product"
import Icon from "@/Components/Icon.vue"

import { remove as loRemove } from 'lodash'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faConciergeBell, faGarage, faExclamationTriangle } from '@fal'
import { routeType } from '@/types/route'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ref } from 'vue'

library.add(faConciergeBell, faGarage, faExclamationTriangle)


const props = defineProps<{
    data: {}
    tab?: string,
    routes: {
        dataList: routeType
        submitAttach: routeType
        detach: routeType
    }
}>()

function productRoute(product: Product) {
    // console.log(route().current())
    switch (route().current()) {

        case "grp.org.shops.show.catalogue.products.index":
        case "grp.org.shops.show.catalogue.collections.show":
        case "grp.org.shops.show.catalogue.dashboard":
            return route(
                'grp.org.shops.show.catalogue.products.show',
                [route().params['organisation'], route().params['shop'], product.slug])
        case 'grp.org.shops.index':
            return route(
                'grp.org.shops.show.catalogue.products.show',
                [route().params['organisation'], product.shop_slug, product.slug])
        case 'grp.org.fulfilments.show.billables.index':
            return route(
                'grp.org.fulfilments.show.billables.show',
                [route().params['organisation'], route().params['fulfilment'], product.slug])
        case 'grp.org.shops.show.catalogue.departments.show':
            return route(
                'grp.org.shops.show.catalogue.departments.show.products.show',
                [route().params['organisation'], route().params['shop'], route().params['department'], product.slug])
        case 'grp.org.shops.show.catalogue.families.show.products.index':
            return route(
                'grp.org.shops.show.catalogue.families.show.products.show',
                [route().params['organisation'], route().params['shop'], route().params['family'], product.slug])
        case 'grp.org.shops.show.catalogue.departments.show.families.show.products.index':
            return route(
                'grp.org.shops.show.catalogue.departments.show.families.show.products.show',
                [route().params['organisation'], route().params['shop'], route().params['department'], route().params['family'], product.slug])
        case 'grp.org.shops.show.catalogue.departments.show.products.index':
            return route(
                'grp.org.shops.show.catalogue.departments.show.products.show',
                [route().params['organisation'], route().params['shop'], route().params['department'], product.slug])
        case 'retina.dropshipping.products.index':
            return route(
                'retina.dropshipping.products.show',
                [product.slug])
        default:
            return null
    }
}


const isLoadingDetach = ref<string[]>([])


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(state)="{ item: product }">
            <Icon :data="product.state"> </Icon>
        </template>

        <template #cell(code)="{ item: product }">
            <Link :href="productRoute(product)" class="primaryLink">
                {{ product['code'] }}
            </Link>
        </template>

        <template #cell(shop_code)="{ item: product }">
            <Link v-if="product['shop_slug']" :href="productRoute(product)" class="secondaryLink">
                {{ product['shop_slug'] }}
            </Link>
        </template>
        
        <template #cell(type)="{ item: product }">
            <Icon :data="product['type_icon']" />
            <Icon :data="product['state_icon']" />
        </template>

        <template #cell(actions)="{ item }">
            <Link
                v-if="routes?.detach?.name"
                :href="route(routes.detach.name, routes.detach.parameters)"
                :method="routes.detach.method"
                @start="() => isLoadingDetach.push('detach' + item.id)"
                @finish="() => loRemove(isLoadingDetach, (xx) => xx == 'detach' + item.id)"
            >
                <Button
                    icon="fal fa-times"
                    type="negative"
                    size="xs"
                    :loading="isLoadingDetach.includes('detach' + item.id)"
                />
            </Link>
            
        </template>
    </Table>
</template>
