<script setup lang="ts">
import { getStyles } from "@/Composables/styles";
import { faCube, faStar } from "@fortawesome/free-solid-svg-icons";
import { library } from "@fortawesome/fontawesome-svg-core";
import "swiper/swiper-bundle.css"; // Swiper styles
import { onMounted, ref, watch } from "vue";

library.add(faCube, faStar);

const props = defineProps<{
	modelValue: any
	webpageData: any
	blockData: Object
}>()

</script>

<template>
  <div class="w-full py-12 px-8 flex gap-x-10 editor-class">
    <div v-html="modelValue.value"></div>
  </div>
</template>

