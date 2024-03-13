<script setup lang="ts">
import Menu1 from './Navbar1/Menu1.vue'
import Menu2 from './Navbar2/Menu2.vue'


const props = defineProps<{
 tools : Object,
 menuDataLayout : object
 activeColumn:string
}>()

const component = {
    'light-theme' : Menu1,
    'dark-theme': Menu2,
}

const emits = defineEmits()
console.log('sfdf',props)
</script>

<template>
    <div>
        <component 
            :is="component[tools.theme]"
            :menuDataLayout="menuDataLayout"
            :tool="tools"
            :activeColumn="activeColumn"
            @changeActiveColumn="(e : string )=>  emits('changeActiveColumn', e)"
        />
    </div>
</template>

<style scoped></style>
