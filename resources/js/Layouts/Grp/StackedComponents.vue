<script setup lang='ts'>
import Button from '@/Components/Elements/Buttons/Button.vue'
import { inject, ref } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTimes } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faTimes)

const layout = inject('layout', layoutStructure)

const getTranslateX = (listLength: number, idxComponent: number) => {
    if (listLength === 1) return 0

    const step = -50 / (listLength - 1)
    return (step * (listLength - (idxComponent + 1))) + (-8 * (listLength - (idxComponent + 1)))
}
</script>

<template>
    <div class="p-6 fixed top-0 left-0 h-screen w-screen flex justify-end isolate z-[100]">
        <!-- <div class="flex gap-x-2 absolute bottom-24 left-1/2 z-[200]">
            <Button @click="() => layout.stackedComponents.push(Profile)" label="Add component" class="">
            </Button>
            <Button @click="() => layout.stackedComponents.pop()" label="Delete component" type="negative" class="">
            </Button>
        </div> -->
        
        <template v-if="layout.stackedComponents.length">
            <TransitionGroup name="stacked-component">
                <div
                    v-for="(component, idxComponent) in layout.stackedComponents"
                    :key="'stackedComponent' + idxComponent"
                    class="absolute top-0 left-0 h-screen w-screen flex justify-end isolate z-[100]"
                >
                    <div @click="layout.stackedComponents.pop()" class="fixed inset-0 bg-black/40 z-10 cursor-pointer" />
                    <div class="py-6 z-20 absolute h-screen w-10/12 transition-all" :style="{
                        backgroundColor: '#fff',
                        transform: `translateX(${getTranslateX(layout.stackedComponents.length, idxComponent)}px)`
                    }">
                        <!-- Button: close -->
                        <div @click="layout.stackedComponents.pop()" class="absolute right-4 top-1 text-gray-400 hover:text-gray-600 cursor-pointer">
                            <FontAwesomeIcon icon='fal fa-times' class='lg' l fixed-width aria-hidden='true' />
                        </div>
                        
                        <!-- Section: main component -->
                        <component :is="component.component" />
                    </div>
                </div>
            </TransitionGroup>
        </template>
    </div>
</template>
