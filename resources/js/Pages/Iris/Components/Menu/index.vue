<template>
    <div>
        <component :is="component[theme]" :navigation="navigation" :saveNav="saveNav" :saveSubMenu="saveSubMenu"/>
    </div>
</template>

<script setup lang="ts">
import Menu1 from './Navbar1/Menu1.vue'
import Menu2 from './Menu2.vue'


const props = defineProps<{
    theme: number,
    navigation : Object
    saveNav : Function
    saveSubMenu : Function
  
}>()

console.log(props)

const component = {
    1: Menu1,
    2: Menu2,
}

</script>

<style scoped></style>