<script setup>

import {Menu, MenuButton, MenuItem, MenuItems} from '@headlessui/vue';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import {trans} from 'laravel-vue-i18n';
import {ref} from 'vue';

const props = defineProps(['shops']);
defineEmits(['select:shop', 'select:shops']);

let currentLabel = ref(props.shops.current.data.name);

let currentSlug = ref(props.shops.current.data.slug ?? null);

</script>

<template>
    <Menu as="div" class="relative inline-block text-left ml-8 w-56">
        <MenuButton class="inline-flex w-full justify-center gap-x-1.5 bg-white px-3 py-1 text-sm  text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">


            <FontAwesomeIcon aria-hidden="true" icon="fal fa-store-alt" class="mr-3"/>
            {{ currentLabel }}
            <FontAwesomeIcon aria-hidden="true" class="ml-4 opacity-50 hover:opacity-100"
                             icon="fal fa-chevron-down"/>


        </MenuButton>
        <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">

            <MenuItems class="absolute w-56  divide-y divide-gray-300  right-0 z-10 mt-1  origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1 ">

                    <MenuItem v-slot="{ active }" v-for="shop in shops.items.data" :key="shop.slug" :disabled="shop.slug===currentSlug?true:null">
                        <button
                            @click="[$emit('select:shop', shop),currentLabel=shop.name,currentSlug=shop.slug]"
                            :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                            {{ shop.name }}
                        </button>
                    </MenuItem>
                </div>
                <div class="py-1 ">
                    <MenuItem v-slot="{ active }">
                        <button
                            @click="[$emit('select:shops'),currentLabel=trans('All Shops'),currentSlug=null]"

                            :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'w-full block px-4 py-2 text-sm']">
                            {{ trans('All Shops') }}
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>

</template>


