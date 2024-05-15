<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { Shop } from "@/types/shop"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle, faDoNotEnter } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { trans } from 'laravel-vue-i18n'
import Tag from '@/Components/Tag.vue'
import { capitalize } from "@/Composables/capitalize"
import { inject } from 'vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'

library.add(faCircle, faDoNotEnter)

const props = defineProps<{
    data: {}
    tab?: string
}>()

const layout = inject('layout', layoutStructure)

function shopRoute(shop: Shop) {
    switch (route().current()) {
        case 'grp.org.shops.index':
            return route(
                'grp.org.shops.show.catalogue.dashboard',
                [route().params['organisation'], shop.slug])
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <!-- Column: State -->
        <template #cell(state)="{item: shop}">
            <div v-if="shop.state === 'open'" v-tooltip="trans('Shop is open')" class="px-1">
                <FontAwesomeIcon icon='fal fa-check' class='text-green-500' fixed-width aria-hidden='true' />
            </div>
            
            <div v-else v-tooltip="trans('Shop is closed')" class="px-1">
                <FontAwesomeIcon icon='fas fa-do-not-enter' class='text-red-400' fixed-width aria-hidden='true' />
            </div>
            
        </template>

        <!-- Column: Code -->
        <template #cell(code)="{ item: shop }">
            <div class="flex">
                <Link :href="shopRoute(shop)" class="primaryLink">
                    {{ shop.code }}
                </Link>
                <div v-if="shop.code == layout.organisationsState?.[layout.currentParams.organisation]?.currentShop" v-tooltip="trans('Recently selected')" class="px-0.5 flex items-center">
                    <FontAwesomeIcon icon='fas fa-circle' class='text-lime-500 text-[6px]' fixed-width aria-hidden='true' />
                </div>
            </div>
        </template>
        
        <!-- Column: Name -->
        <template #cell(name)="{item: shop}">
            <div :class="shop.state !== 'open' ? 'line-through text-gray-400' : ''">
                {{ shop.name }}
            </div>
        </template>

    </Table>
</template>


