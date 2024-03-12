

<script setup lang="ts">
import FooterTheme1 from './FooterTheme1.vue'
import FooterTheme2 from './FooterTheme2.vue'
import FooterTheme3 from './FooterTheme3.vue'
import { ref } from 'vue';

const props = defineProps<{
 tools : Object,
 footerDataLayout : object
}>()

const component = {
    'light-theme' : FooterTheme2,
    'dark-theme': FooterTheme1,
    'simple-theme': FooterTheme3,
}

const activeColumn = ref(null)



</script>

<template>
    <div>
        <component 
            :is="component[tools.theme]"
            :footerDataLayout="footerDataLayout"
            :tool="tools"
            :activeColumn="activeColumn"
        />
    </div>
</template>

<style scoped></style>
