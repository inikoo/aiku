<script setup lang="ts">
import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
import { useColorTheme } from '@/Composables/useStockList'
import ColorSchemeWorkshopWebsite from '@/Components/CMS/Website/Layout/ColorSchemeWorkshopWebsite.vue'
import { routeType } from '@/types/route'
import { Link } from '@inertiajs/vue3'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { useFontFamilyList } from '@/Composables/useFont'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'

import { ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPaintBrushAlt } from '@fal'
import { faRocketLaunch } from '@far'
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
        updateColorRoute: routeType
        color: object
    }
}>()

const listColorTheme = [...useColorTheme]

const selectedColor = ref(props.data.color?.color ? props.data.color?.color : [...listColorTheme[0]])
const selectedIndex = ref(0) // Track the selected index
const selectedLayout = ref('fullscreen') // Default layout option
const fontFamily = ref("Inter, sans-serif")

const onClickColor = (colorTheme: string[], index: number) => {
    selectedColor.value = [...colorTheme]
    selectedIndex.value = index // Set the selected index
}
</script>

<template>
    <div class="p-8 grid grid-cols-4 gap-6 bg-gray-50 rounded-lg h-[79vh]">
        <!-- Theme Selector -->
        <div class="space-y-6 col-span-1 p-4 bg-white rounded-lg shadow-md relative h-full">
            <!-- Header -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                    <span class="whitespace-nowrap text-sm text-gray-600 font-semibold">Select Theme</span>
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                </div>

                <!-- Color Options -->
                <div class="flex flex-wrap justify-center gap-3">
                    <div v-for="(colorTheme, index) in listColorTheme" :key="index"
                        @click="onClickColor(colorTheme, index)"
                        class="flex ring-1 ring-gray-300 transition duration-300 rounded-md overflow-hidden cursor-pointer"
                        :class="{ 'ring-2 ring-indigo-500': selectedIndex === index }">
                        <div class="h-6 w-6" :style="{ backgroundColor: colorTheme[0] }"></div>
                        <div class="h-6 w-6" :style="{ backgroundColor: colorTheme[1] }"></div>
                        <div class="h-6 w-6" :style="{ backgroundColor: colorTheme[2] }"></div>
                        <div class="h-6 w-6" :style="{ backgroundColor: colorTheme[3] }"></div>
                        <div class="h-6 w-6" :style="{ backgroundColor: colorTheme[4] }"></div>
                        <div class="h-6 w-6" :style="{ backgroundColor: colorTheme[5] }"></div>
                    </div>
                </div>
            </div>

            <!-- Layout Selector -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                    <span class="whitespace-nowrap text-sm text-gray-600 font-semibold">Select Layout</span>
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                </div>

                <!-- Radio Options for Layout -->
                <div class="flex gap-4 justify-center">
                    <!-- Fullscreen Layout Option -->
                    <label
                        class="flex flex-col items-center gap-2 p-4 border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition"
                        :class="{ 'border-indigo-500 bg-indigo-50': selectedLayout === 'fullscreen' }">
                        <input type="radio" value="fullscreen" v-model="selectedLayout" class="hidden">
                        <div class="w-20 h-12 bg-gray-200 rounded-md flex items-center justify-center">
                            <div class="w-full h-full"
                                style="background: repeating-linear-gradient(45deg, #ebf8ff, #ebf8ff 10px, #bee3f8 10px, #bee3f8 20px);">
                            </div>
                        </div>
                        <span class="text-sm font-semibold">Fullscreen</span>
                    </label>

                    <!-- Blog in the Middle Layout Option -->
                    <label
                        class="flex flex-col items-center gap-2 p-4 border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition"
                        :class="{ 'border-indigo-500 bg-indigo-50': selectedLayout === 'blog' }">
                        <input type="radio" value="blog" v-model="selectedLayout" class="hidden">
                        <div class="w-20 h-12 bg-gray-200 rounded-md flex items-center justify-center">
                            <div class="w-[60%] h-full rounded"
                                style="background: repeating-linear-gradient(45deg, #ebf8ff, #ebf8ff 10px, #bee3f8 10px, #bee3f8 20px);">
                            </div>
                        </div>
                        <span class="text-sm font-semibold">Middle</span>
                    </label>
                </div>
            </div>

            <!-- Font Family Selector -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                    <span class="whitespace-nowrap text-sm text-gray-600 font-semibold">Font Family</span>
                    <hr class="h-0.5 rounded-full w-full bg-gray-300" />
                </div>

                <div class="flex flex-wrap justify-center gap-3">
                    <PureMultiselect v-model="fontFamily" required :options="useFontFamilyList">
                        <template #option="{ option, isSelected, isPointed, search }">
                            <span :style="{ fontFamily: option.value }">{{ option.label }}</span>
                        </template>
                        <template #label="{ value }">
                            <div class="multiselect-single-label" :style="{ fontFamily: value.value }">{{ value.label }}
                            </div>
                        </template>
                    </PureMultiselect>
                </div>
            </div>

            <!-- Publish Button at the Bottom with Absolute Positioning -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t">
                <Link :href="route(data.updateColorRoute.name, data.updateColorRoute.parameters)" method="patch"
                    as="button" :data="{ layout: { color: selectedColor, fontFamily : fontFamily, layout :  selectedLayout }}" class="w-full">
                <Button type="submit" full label="Publish" :icon="faRocketLaunch" />
                </Link>
            </div>
        </div>


        <!-- Workshop Preview -->
        <div class="space-y-6 col-span-3 ">
            <div  class="rounded-lg shadow-md bg-white p-8 h-full flex justify-center ">
                <ColorSchemeWorkshopWebsite 
                    :routeList="data.routeList" 
                    :color="selectedColor" 
                    :layout="selectedLayout"
                    :fontFamily="fontFamily"
                />
            </div>
          
        </div>
    </div>
</template>
