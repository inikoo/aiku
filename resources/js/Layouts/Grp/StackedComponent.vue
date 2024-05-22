<script setup lang='ts'>
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { inject, type Component } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTimes } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faTimes)

const props = defineProps<{
    component: Component
    idxComponent: number
    listLength: number
}>()

const layout = inject('layout', layoutStructure)

function getTranslateX(listLength: number, idxComponent: number) {
    if (listLength === 1) return 0

    const step = -50 / (listLength - 1)
    return (step * (listLength - (idxComponent + 1))) + (-8 * (listLength - (idxComponent + 1)))
}

</script>

<template>
    <div class="absolute top-0 left-0 h-screen w-screen flex justify-end isolate z-[100]">
        <div @click="layout.stackedComponents.pop()" class="fixed inset-0 bg-black/20 z-10 cursor-pointer" />

        <div class="z-20 absolute h-screen w-10/12 transition-all" :style="{
            backgroundColor: '#fff',
            transform: `translateX(${getTranslateX(listLength, idxComponent)}px)`
        }">
            <!-- Button: close -->
            <div @click="layout.stackedComponents.pop()" class="absolute right-4 top-2 text-gray-400 hover:text-gray-600 cursor-pointer">
                <FontAwesomeIcon icon='fal fa-times' class='lg' l fixed-width aria-hidden='true' />
            </div>
            <!-- <span class="ml-4 text-gray-500">
                {{ idxComponent }}
            </span> -->
            <component :is="component" />
        </div>

    </div>
</template>