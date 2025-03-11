<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed } from "vue";
import { faPresentation, faLink, faPaperclip } from "@fal";
import { library } from "@fortawesome/fontawesome-svg-core";
import { getStyles } from "@/Composables/styles.js";
import { v4 as uuidv4 } from "uuid";

library.add(faPresentation, faLink, faPaperclip);

const props = defineProps<{
  fieldValue: any;
  webpageData?: any;
  blockData?: Object;
}>();

const screenWidth = ref(window.innerWidth);

// Fungsi untuk memperbarui ukuran layar saat berubah
const updateScreenWidth = () => {
  screenWidth.value = window.innerWidth;
};

onMounted(() => {
  window.addEventListener("resize", updateScreenWidth);
});

onUnmounted(() => {
  window.removeEventListener("resize", updateScreenWidth);
});

// Menentukan ukuran berdasarkan kondisi Mobile/Desktop
const iframeStyles = computed(() => {
  const baseStyles = getStyles(props.fieldValue?.container?.properties) || {};

  if (props.fieldValue?.link?.includes("wowsbar")) {
    if (screenWidth.value <= 768) {
      // Mobile (≤768px)
      return {
        ...baseStyles,
        width: "100%",
        height: "26vh", // Bisa diatur sesuai kebutuhan
      };
    } 
  }

  return baseStyles;
});
</script>

<template>
  <iframe 
    :title="fieldValue?.title || `iframe-${uuidv4()}`"
    :src="fieldValue?.link" 
    :style="iframeStyles"
  />
</template>
