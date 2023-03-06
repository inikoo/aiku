<script setup>

import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";

defineProps(['shops', 'shopsName']);
defineEmits(['change:shop']);


</script>

<template>
    <Menu as="div" class="relative inline-block text-left ml-5 pl-8" >
        <div v-for="(shop, shopIndex) in shops" :key="shopIndex" v-if="shopIndex === 0">
            <MenuButton>
                {{shop.name}}

                <font-awesome-icon aria-hidden="true" class="ml-4 opacity-50 hover:opacity-100"
                                   icon="fal fa-chevron-down" />
            </MenuButton>
        </div>
        <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems
                class="absolute right-32 z-10 mt-2  origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <MenuItem v-slot="{ active }" v-for="(shop, shopIndex) in shops" :key="shopIndex">
                        <button
                            @click="$emit('change:shop', shop)"
                            href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">
                            {{shop.name}}
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>

</template>


