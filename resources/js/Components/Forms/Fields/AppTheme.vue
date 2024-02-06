<script setup lang='ts'>
// import { ColorPicker } from 'vue-color-kit'
// import 'vue-color-kit/dist/vue-color-kit.css'
import { useColorTheme } from '@/Composables/useStockList'
import { useLayoutStore } from '@/Stores/layout'

const layoutStore = useLayoutStore()

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData: {
    }
}>()

const onClickColor = (colorTheme: string[]) => {
    layoutStore.app.theme = colorTheme
    props.form[props.fieldName] = colorTheme
}

</script>

<template>
    <div class="relative w-full">
        <div class="flex flex-col gap-x-2 gap-y-3">
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

        <!-- <ColorPicker theme="dark" :color="bgColor" :colors-default="useSolidColor" @changeColor="(e) => onChangeColor(e)"/> -->
    </div>
</template>