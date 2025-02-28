<script setup lang="ts">
import SliderLandscape from "@/Components/Banners/Slider/SliderLandscape.vue";
import SliderSquare from "@/Components/Banners/Slider/SliderSquare.vue";
import { getStyles } from "@/Composables/styles";


const props = defineProps<{ 
    fieldValue: { 
        compiled_layout?: any, 
        container : { 
            properties : any
        }
    }
}>();

</script>

<template>
    <div :style="getStyles(fieldValue?.container?.properties)">
        <SliderLandscape v-if="fieldValue.compiled_layout.type === 'landscape'" :data="fieldValue.compiled_layout"
            :production="true" />
        <SliderSquare v-else :data="fieldValue.compiled_layout" :production="true" />
    </div>
</template>