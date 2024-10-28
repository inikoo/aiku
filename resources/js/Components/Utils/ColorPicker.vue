<script setup lang="ts">
import { ref } from 'vue'
import OverlayPanel from 'primevue/overlaypanel'
import { ColorPicker } from 'vue-color-kit'
import 'vue-color-kit/dist/vue-color-kit.css'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faTimes } from '@fortawesome/free-solid-svg-icons'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faTimes)

interface RGBA {
    r: number
    g: number
    b: number
    a: number
}

interface HSV {
    h: number
    s: number
    v: number
}

interface Color {
    rgba: RGBA
    hsv: HSV
    hex: string
}

// Props and emits setup
const props = withDefaults(defineProps<{
    color: string
    closeButton?: boolean
}>(), {
    color: 'rgba(0, 0, 0, 0)'
})

const emits = defineEmits<{
    (e: 'changeColor', value: Color): void
}>()

// Ref for OverlayPanel
const overlayPanel = ref<null | InstanceType<typeof OverlayPanel>>(null)

// Helper function: converts opacity to hexadecimal
const opacityToHexCode = (opacity: number) => {
    const alphaValue = Math.round(opacity * 255)
    return alphaValue.toString(16).padStart(2, '0')
}
</script>

<template>
    <div class="relative">
        <!-- Toggle button -->
        <div
            v-bind="$attrs"
            class="h-12 w-12 cursor-pointer"
            :style="{ backgroundColor: color }"
            @click="overlayPanel.show($event)"
        ></div>

        <!-- OverlayPanel with ColorPicker -->
        <OverlayPanel ref="overlayPanel" class="shadow-lg rounded-md">
            <div class="relative">
                <ColorPicker
                    style="width: 220px;"
                    theme="dark"
                    :color="color"
                    :sucker-hide="true"
                    @changeColor="(e) => {emits('changeColor', {...e, hex: e.hex + opacityToHexCode(e.rgba.a)}),overlayPanel.hide()}"
                />
                
                <div @click="overlayPanel.hide()" class="absolute top-0 right-0 mt-1 mr-1">
                    <FontAwesomeIcon icon="faTimes" class="text-gray-400 hover:text-gray-600 cursor-pointer" fixed-width aria-hidden="true" />
                </div>
            </div>
        </OverlayPanel>
    </div>
</template>
