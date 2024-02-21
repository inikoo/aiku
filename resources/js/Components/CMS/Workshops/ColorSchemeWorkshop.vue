<script setup lang="ts">
import ColorSchemeWorkshopWebsite from '@/Components/CMS/Workshops/ColorSchemeWorkshopWebsite.vue'
import { useColorTheme } from '@/Composables/useStockList'
import { ref } from 'vue'

const props = defineProps<{
    data: any
}>()

const selectedColor = ref(useColorTheme[0])
const onClickColor = (colorTheme: string[]) => {
    selectedColor.value = colorTheme
}

</script>

<template>
    {{ props }}
    <div class="p-8 grid grid-cols-2">
        <div>
            <div class="w-fit">
                <div class="mx-auto text-center">Main Layout</div>
                <div class="border border-gray-300 px-3 py-2 space-y-2 rounded-md w-64 text-zinc-700">
                    <div>
                        <div class="text-sm">Background color</div>
                        <div class="flex gap-x-2">
                            <div class="bg-indigo-500 h-7 aspect-square rounded" />
                            <div>#6601FF</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm">Text color</div>
                        <div class="flex gap-x-2">
                            <div class="bg-gray-100 h-7 aspect-square rounded" />
                            <div>#EEEEEEE</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-fit">
                <div class="mx-auto text-center">Navigation and box</div>
                <div class="border border-gray-300 px-3 py-2 space-y-2 rounded-md w-64 text-zinc-700">
                    <div>
                        <div class="text-sm">Background color</div>
                        <div class="flex gap-x-2">
                            <div class="bg-yellow-400 h-7 aspect-square rounded" />
                            <div>#FACC16</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm">Text color</div>
                        <div class="flex gap-x-2">
                            <div class="bg-gray-800 h-7 aspect-square rounded" />
                            <div>#181818</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-fit">
                <div class="mx-auto text-center">Button and mini box</div>
                <div class="border border-gray-300 px-3 py-2 space-y-2 rounded-md w-64 text-zinc-700">
                    <div>
                        <div class="text-sm">Background color</div>
                        <div class="flex gap-x-2">
                            <div class="bg-rose-500 h-7 aspect-square rounded" />
                            <div>#CC0000</div>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm">Text color</div>
                        <div class="flex gap-x-2">
                            <div class="bg-gray-100 h-7 aspect-square rounded" />
                            <div>#EEEEEEE</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <ColorSchemeWorkshopWebsite :color="selectedColor" />

            <div class="space-y-4">
                <div class="flex items-center gap-x-2">
                    <hr class="h-0.5 rounded-full w-full bg-gray-300"/>
                    <span class="whitespace-nowrap text-sm text-gray-500">Select theme</span>
                    <hr class="h-0.5 rounded-full w-full bg-gray-300"/>
                </div>
                <div class="flex flex-wrap justify-center gap-x-2 gap-y-3">
                    <div v-for="colorTheme in useColorTheme" @click="() => onClickColor(colorTheme)"
                        class="flex ring-1 ring-gray-400 hover:ring-indigo-500 shadow rounded overflow-hidden w-fit cursor-pointer"
                    >
                        <div class="h-6 aspect-square" :style="{backgroundColor: colorTheme[0]}" />
                        <div class="h-6 aspect-square" :style="{backgroundColor: colorTheme[1]}" />
                        <div class="h-6 aspect-square" :style="{backgroundColor: colorTheme[2]}" />
                        <div class="h-6 aspect-square" :style="{backgroundColor: colorTheme[3]}" />
                        <div class="h-6 aspect-square" :style="{backgroundColor: colorTheme[4]}" />
                        <div class="h-6 aspect-square" :style="{backgroundColor: colorTheme[5]}" />
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
