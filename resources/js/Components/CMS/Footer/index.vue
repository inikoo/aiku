<template>
    <div>
        <component :is="component[theme]" :columnSelected="columnSelected" :social="props.social" :saveSocialmedia="saveSocialmedia"
            :navigation="props.navigation" :changeColumns="props.changeColumns" :copyRight="copyRight" :copyRightSave="copyRightSave"
            :selectedColumns="selectedColumns" :saveItemTitle="saveItemTitle" :saveTextArea="saveTextArea" :saveInfo="saveInfo"
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
    changeColumns: Function,
    selectedColumns: Function,
    columnSelected: Object
    saveItemTitle: Function
    saveTextArea:Function
    tool:Object
    saveLink:Function
    saveInfo:Function
    copyRight : Object
    copyRightSave : Function
    saveSocialmedia : Function
}>()

console.log(props)

const component = {
    1: FooterTheme1,
    2: FooterTheme2,
    3: FooterTheme3,
}

</script>

<style scoped></style>
