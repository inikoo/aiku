<script setup lang='ts'>
// import { ColorPicker } from 'vue-color-kit'
// import 'vue-color-kit/dist/vue-color-kit.css'
import { ref } from 'vue'
import { useSolidColor } from '@/Composables/useStockList'
import { Colors } from '@/types/Color'
import { useLayoutStore } from '@/Stores/layout'

const layoutStore = useLayoutStore()

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData: {
    }
}>()

// const bgColor = ref('#1BA7DB')

// const onChangeColor = (colors: Colors) => {
//     props.form[props.fieldName] = colors
//     layoutStore.app.color = colors
// }

// 0: Leftsidebar, 1: NavigationSimple, 2: Text for LeftSideber, 3: Navigation & underline, 4: Text for Navigation, 5: -
const useColorTheme = [
    ['#4f46e5', '#f3f4f6', '#4f46e5', '#facc15', '#334155', '#64748b'],
    ['#f59e0b', '#ffffff', '#f59e0b', '#a3e635', '#334155', '#e2e8f0'],
    ['#334155', '#ffffff', '#334155', '#38bdf8', '#334155', '#cbd5e1'],
    ['#EEF5DB', '#64748b', '#ffffff', '#FE5F55', '#334155', '#b8d8d8'],
]
</script>

<template>
    <div class="relative w-full">
        <div class="flex flex-col gap-x-2 gap-y-3">
            <div v-for="colorTheme in useColorTheme" @click="() => layoutStore.app.theme = colorTheme"
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

        <!-- <ColorPicker theme="dark" :color="bgColor" :colors-default="useSolidColor" @changeColor="(e) => onChangeColor(e)"/> -->
    </div>
</template>