<template>
    <div>
        <component :is="component[theme]" />
    </div>
</template>

<script setup lang="ts">
import FooterTheme1 from './FooterTheme1.vue'
import FooterTheme2 from './FooterTheme2.vue'
import FooterTheme3 from './FooterTheme3.vue'

const props = defineProps<{
    theme: number
}>()

const component = {
    1: FooterTheme1, 
    2: FooterTheme2, 
    3: FooterTheme3, 
}

</script>

<style scoped>

</style>