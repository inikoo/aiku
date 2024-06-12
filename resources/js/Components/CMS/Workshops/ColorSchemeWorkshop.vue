<script setup lang="ts">
import ColorSchemeWorkshopWebsite from '@/Components/CMS/Workshops/ColorSchemeWorkshopWebsite.vue'
import { useColorTheme } from '@/Composables/useStockList'
import { ref } from 'vue'
import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPaintBrushAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import {useLayoutStore} from "@/Stores/layout";
library.add(faPaintBrushAlt)

const props = defineProps<{
    data: any
}>()

const listColorTheme = [...useColorTheme]

const selectedColor = ref([...useColorTheme[0]])

const onClickColor = (colorTheme: string[]) => {
    selectedColor.value = colorTheme
}

</script>

<template>
    <div class="p-8 grid grid-cols-2">
        <div class="space-y-4">
            <div class="w-fit">
                <div class="text-sm text-center text-gray-500">Main Layout</div>
                <div class="border border-gray-300 px-3 py-2 space-y-2 rounded-md w-64 text-zinc-700">
                    <div>
                        <div class="text-sm">Background color</div>
                        <div class="flex gap-x-2">
                            <ColorPicker class="h-7 aspect-square rounded shadow flex items-center justify-center" :color="selectedColor[0]" @changeColor="(e) => selectedColor[0] = e.hex">
                                <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-sm text-gray-400' fixed-width aria-hidden='true' />
                            </ColorPicker>
                            <div>{{ selectedColor[0] }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm">Text color</div>
                        <div class="flex gap-x-2">
                            <ColorPicker class="h-7 aspect-square rounded shadow flex items-center justify-center" :color="selectedColor[1]" @changeColor="(e) => selectedColor[1] = e.hex">
                                <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-sm text-gray-400' fixed-width aria-hidden='true' />
                            </ColorPicker>
                            <div>{{ selectedColor[1] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-fit">
                <div class="text-sm text-center text-gray-500">Navigation and box</div>
                <div class="border border-gray-300 px-3 py-2 space-y-2 rounded-md w-64 text-zinc-700">
                    <div>
                        <div class="text-sm">Background color</div>
                        <div class="flex gap-x-2">
                            <ColorPicker class="h-7 aspect-square rounded shadow flex items-center justify-center" :color="selectedColor[2]" @changeColor="(e) => selectedColor[2] = e.hex">
                                <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-sm text-gray-400' fixed-width aria-hidden='true' />
                            </ColorPicker>
                            <div>{{ selectedColor[2] }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm">Text color</div>
                        <div class="flex gap-x-2">
                            <ColorPicker class="h-7 aspect-square rounded shadow flex items-center justify-center" :color="selectedColor[3]" @changeColor="(e) => selectedColor[3] = e.hex">
                                <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-sm text-gray-400' fixed-width aria-hidden='true' />
                            </ColorPicker>
                            <div>{{ selectedColor[3] }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-fit">
                <div class="text-sm text-center text-gray-500">Button and mini box</div>
                <div class="border border-gray-300 px-3 py-2 space-y-2 rounded-md w-64 text-zinc-700">
                    <div>
                        <div class="text-sm">Background color</div>
                        <div class="flex gap-x-2">
                            <ColorPicker class="h-7 aspect-square rounded shadow flex items-center justify-center" :color="selectedColor[4]" @changeColor="(e) => selectedColor[4] = e.hex">
                                <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-sm text-gray-400' fixed-width aria-hidden='true' />
                            </ColorPicker>
                            <div>{{ selectedColor[4] }}</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm">Text color</div>
                        <div class="flex gap-x-2">
                            <ColorPicker class="h-7 aspect-square rounded shadow flex items-center justify-center" :color="selectedColor[5]" @changeColor="(e) => selectedColor[5] = e.hex">
                                <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-sm text-gray-400' fixed-width aria-hidden='true' />
                            </ColorPicker>
                            <div>{{ selectedColor[5] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <ColorSchemeWorkshopWebsite :color="selectedColor" />

            <div class="space-y-4">
                <div class="flex items-center gap-x-2">
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                    <span class="whitespace-nowrap text-sm text-gray-500">Select theme</span>
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                </div>
                <div class="flex flex-wrap justify-center gap-x-2 gap-y-3">
                    <div v-for="colorTheme in listColorTheme" @click="() => onClickColor(colorTheme)"
                        class="flex ring-1 ring-gray-400 hover:ring-indigo-500 shadow rounded overflow-hidden w-fit cursor-pointer">
                        <div class="h-6 aspect-square" :style="{ backgroundColor: colorTheme[0] }" />
                        <div class="h-6 aspect-square" :style="{ backgroundColor: colorTheme[1] }" />
                        <div class="h-6 aspect-square" :style="{ backgroundColor: colorTheme[2] }" />
                        <div class="h-6 aspect-square" :style="{ backgroundColor: colorTheme[3] }" />
                        <div class="h-6 aspect-square" :style="{ backgroundColor: colorTheme[4] }" />
                        <div class="h-6 aspect-square" :style="{ backgroundColor: colorTheme[5] }" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
