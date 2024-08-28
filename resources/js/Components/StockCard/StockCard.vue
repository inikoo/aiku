<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { trans } from 'laravel-vue-i18n'
import { ref } from 'vue'
import InfoCard from '@/Components/StockCard/InfoCard.vue'
import EditLocationCard from '@/Components/StockCard/EditLocationCard.vue'
import StockCheckCard from '@/Components/StockCard/StockCheckCard.vue'
import MoveStockCard from '@/Components/StockCard/MoveStock.vue'
import Button from "@/Components/Elements/Buttons/Button.vue";


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faShoppingBasket, faClock, faEllipsisV, } from '@far'
import { faStickyNote, faClipboard, faInventory, faForklift } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'



library.add(faShoppingBasket, faStickyNote, faClock, faEllipsisV, faClipboard, faInventory, faForklift)

const props = defineProps<{
    data: object,
    locationRoute: routeType
}>();

const menu = [
    {
        label: 'Stock Check',
        key: 'stockCheck',
        icon: faClipboard
    },
    {
        label: 'Move Stock',
        key: 'moveStock',
        icon: faForklift
    },
    {
        label: 'Edit Location',
        key: 'editLocation',
        icon: faInventory
    },
]


const getComponent = (componentName: string) => {
    const components = {
        'stockCheck': StockCheckCard,
        'moveStock': MoveStockCard,
        'editLocation': EditLocationCard,
    };
    return components[componentName?.key] ?? InfoCard
};


const activeMenu = ref(null)

</script>


<template>
    <div class="flex justify-between border-b border-gray-300 p-2">
        <div class="font-semibold flex gap-3">
            <span>{{ trans("Contact Card") }}</span>
            <div class="text-xs my-auto" v-if="activeMenu">
                <FontAwesomeIcon :icon="activeMenu.icon" class="mr-2" aria-hidden="true" />
                <span>{{ activeMenu.label }}</span>
            </div>
        </div>

        <!-- Hover menu implementation -->
        <Menu v-if="!activeMenu" as="div" class="relative inline-block text-left" @mouseleave="activeMenu = null">
            <div>
                <MenuButton
                    @mouseover="activeMenu = null"
                    class="inline-flex w-full justify-center rounded-md px-4 py-2 text-sm font-medium hover:bg-black/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75">
                    <FontAwesomeIcon :icon="faEllipsisV" />
                </MenuButton>
            </div>

            <transition enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0">
                <MenuItems
                    v-show="!activeMenu"
                    @mouseover="activeMenu = null"
                    class="absolute right-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg z-30 ring-1 ring-black/5 focus:outline-none">
                    <div v-for="item in menu" class="px-1 py-1 border-b-1" :key="item.key">
                        <MenuItem>
                            <button @click="() => activeMenu = item"
                                :class="['text-gray-900 group flex w-full items-center rounded-md px-2 py-2 text-sm']">
                                <FontAwesomeIcon :icon="item.icon" class="mr-2 h-5 w-5" aria-hidden="true" />
                                {{ item.label }}
                            </button>
                        </MenuItem>
                    </div>
                </MenuItems>
            </transition>
        </Menu>

        <div v-else  class="flex justify-between gap-2">
            <Button type='tertiary' label="Cancel" size="xs" @click="() => activeMenu = null" />
        </div>
    </div>

    <div class="mt-2 flow-root">
        <component :is="getComponent(activeMenu)" :data="data" :locationRoute="locationRoute" />
    </div>
</template>
