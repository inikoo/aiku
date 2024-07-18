<script setup lang='ts'>
import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
import { useColorTheme } from '@/Composables/useStockList'
import ColorSchemeWorkshopWebsite from '@/Components/Websites/Layout/ColorSchemeWorkshopWebsite.vue'
import { routeType } from '@/types/route'
import { Link } from '@inertiajs/vue3'
import Button from '@/Components/Elements/Buttons/Button.vue'

import { ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPaintBrushAlt } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faPaintBrushAlt)


const props = defineProps<{
    data: {
        routeList: {
            headerRoute: routeType
            footerRoute: routeType
            webpageRoute: routeType
            notificationRoute: routeType
            menuLeftRoute: routeType
            menuRightRoute: routeType
            menuRoute: routeType
        }
        updateColorRoute : routeType
        color: object
    }

}>()

console.log(props)

const listColorTheme = [...useColorTheme]

const selectedColor = ref(props.data.color?.color ? props.data.color?.color :  [...listColorTheme[0]])

const onClickColor = (colorTheme: string[]) => {
    selectedColor.value = [...colorTheme]
}
</script>

<template>
    <div class="p-8 grid grid-cols-2">
        <div class="space-y-6">
            <div class="w-fit flex justify-end">
                    <Link :href="route(data.updateColorRoute.name,data.updateColorRoute.parameters)" method="patch" as="button" :data="{ layout: {color: selectedColor} }">
                        <Button label="apply" size="xs"  icon="fas fa-rocket" ></Button>
                    </Link>
                </div>
            <div class="w-fit ">
                <div class="text-sm text-gray-500 font-semibold">Main Layout</div>
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
                <div class="text-sm text-gray-500 font-semibold">Navigation and box</div>
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
                <div class="text-sm text-gray-500 font-semibold">Button and mini box</div>
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
            <ColorSchemeWorkshopWebsite
                :routeList="data.routeList"
                :color="selectedColor"
            />

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
