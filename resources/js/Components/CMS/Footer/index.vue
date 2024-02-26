

<script setup lang="ts">
import FooterTheme1 from './FooterTheme1.vue'
import FooterTheme2 from './FooterTheme2.vue'
import FooterTheme3 from './FooterTheme3.vue'

const props = defineProps<{
 tools : Object,
 footerDataLayout : object
}>()

console.log('iniii',props.footerDataLayout)
const component = {
    'light-theme' : FooterTheme2,
    'dark-theme': FooterTheme1,
    'simple-theme': FooterTheme3,
}

</script>

<template>
    <div>
        <component 
            :is="component[tools.theme]"
            :footerDataLayout="footerDataLayout"
            :tool="tools"
        />
    </div>
</template>

<style scoped></style>
