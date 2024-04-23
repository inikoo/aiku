

<script setup lang="ts">
import FooterTheme1 from './FooterTheme1.vue'
import FooterTheme2 from './FooterTheme2.vue'
import FooterTheme3 from './FooterTheme3.vue'
import { ref, watch } from "vue"

const props = defineProps<{
 tools : Object,
 footerDataLayout : object
 activeColumn:string
}>()

const component = {
    'light-theme' : FooterTheme2,
    'dark-theme': FooterTheme1,
    'simple-theme': FooterTheme3,
}

const emits = defineEmits()

</script>

<template>
    <div>
        <component 
            :is="component[tools.theme]"
            :footerDataLayout="footerDataLayout"
            :tool="tools"
            :activeColumn="activeColumn"
            @changeActiveColumn="(e : string )=>  emits('changeActiveColumn', e)"
        />
    </div>
</template>

<style scoped></style>
