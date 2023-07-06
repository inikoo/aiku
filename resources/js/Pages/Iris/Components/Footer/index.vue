<template>
    <div>
        <component :is="component[theme]" :columSelected="columSelected" :social="props.social"
            :navigation="props.navigation" :changeColums="props.changeColums"
            :selectedColums="selectedColums" :saveItemTitle="saveItemTitle" :saveTextArea="saveTextArea"
            :tool="tool" :saveLink="saveLink"/>
    </div>
</template>

<script setup lang="ts">
import FooterTheme1 from './FooterTheme1.vue'
import FooterTheme2 from './FooterTheme2.vue'
import FooterTheme3 from './FooterTheme3.vue'

const props = defineProps<{
    theme: number,
    social: Object,
    navigation: Object,
    changeColums: Function,
    selectedColums: Function,
    columSelected: Object
    saveItemTitle: Function
    saveTextArea:Function
    tool:Object
    saveLink:Function
}>()

console.log(props)

const component = {
    1: FooterTheme1,
    2: FooterTheme2,
    3: FooterTheme3,
}

</script>

<style scoped></style>