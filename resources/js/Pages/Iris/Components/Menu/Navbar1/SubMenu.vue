<script setup lang="ts">
import { ref, watchEffect } from "vue"
import { router } from "@inertiajs/vue3"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faBars, faMagnifyingGlass } from "@fortawesome/free-solid-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import draggable from "vuedraggable"
import Hyperlink from '../../Fields/Hyperlink.vue'
library.add(faBars, faMagnifyingGlass)

const props = defineProps<{
    data: Object
    saveSubMenu: Function
    closePopover : Function
}>()
</script>

<template>
    <div class="ml-2 cursor-pointer text-rose-500" @click="closePopover">x</div>
    <div class="absolute inset-x-0 top-full text-sm text-gray-500">
        <div class="absolute inset-0 top-1/2 bg-white shadow" aria-hidden="true"></div>
        <div class="relative bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div>
                    <draggable :list="data.featured
                        " :group="`menu${data.name}`" key="id" class="grid grid-cols-3 gap-x-8 gap-y-4 py-4">
                        <template v-slot:item="{ element: child, index }">
                            <div class="group relative">
                                <div class="mt-4 block font-medium text-gray-900 p-2">
                                    <span class="absolute inset-0 z-10" aria-hidden="true">
                                        <Hyperlink :data="child" valueKeyLabel="name" valueKeyLink="link" :useDelete="true"
                                            :save="(value)=>saveSubMenu({...value, parentId : data.id })" />
                                    </span>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>
            </div>
        </div>
    </div>
</template>
