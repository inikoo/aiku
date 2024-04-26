

<script setup lang="ts">
import HeaderTheme1 from './HeaderTheme1.vue';
import HeaderTheme2 from './HeaderTheme2.vue';

const props = defineProps<{
    theme: String,
    data: Object
    layout : Object
}>()

const emits = defineEmits();
const component = {
   "simpleSticky": HeaderTheme1,
   "simple": HeaderTheme2,
}

const changeLogo=(file)=>{
    emits('changeLogo',file)
}

</script>

<template>
    <div>
        <component :is="component[theme]" :data="data" @changeLogo="changeLogo" :layout="layout"/>
    </div>
</template>

<style scoped></style>