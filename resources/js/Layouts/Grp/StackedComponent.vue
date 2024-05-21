<script setup lang='ts'>
import type { Component } from 'vue'

const props = defineProps<{
    component: Component
    idxComponent: number
    listLength: number
}>()

function getTranslateX(listLength: number, idxComponent: number) {
    if (listLength === 1) return 0

    const step = -50 / (listLength - 1)

    console.log('eew', idxComponent, step * (listLength - (idxComponent + 1)))

    return (step * (listLength - (idxComponent + 1))) + (-8 * (listLength - (idxComponent + 1)))
}

</script>

<template>
    <div class="absolute top-0 left-0 h-screen w-screen flex justify-end isolate z-[100]">
        <div class="fixed inset-0 bg-black/20 z-10" />

        <div class="z-20 absolute h-screen w-10/12 transition-all" :style="{
            backgroundColor: '#fff',
            transform: `translateX(${getTranslateX(listLength, idxComponent)}px)`
        }">

            <!-- <span class="ml-4 text-gray-500">
                {{ idxComponent }}
            </span> -->
            <component :is="component" />
        </div>

    </div>
</template>