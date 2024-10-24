<script setup lang="ts">
import { getStyles } from "@/Composables/styles";
import { faCube, faStar } from "@fortawesome/free-solid-svg-icons";
import { library } from "@fortawesome/fontawesome-svg-core";
import "swiper/swiper-bundle.css"; // Swiper styles
import { onMounted, ref, watch } from "vue";

library.add(faCube, faStar);

// Props
const props = defineProps<{
  modelValue: { value: string },
  properties: {}
}>();

// Ref to hold the rendered HTML (without <script> tags)
const renderedHTML = ref("");

const injectScripts = (htmlString: string) => {
  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = htmlString;
  const scripts = tempDiv.querySelectorAll("script");
  scripts.forEach((script) => {
    const newScript = document.createElement("script");
    if (script.src) {
      newScript.src = script.src;
    } else {
      newScript.innerHTML = script.innerHTML;
    }
    document.body.appendChild(newScript);
  });

  scripts.forEach((script) => script.remove());
  return tempDiv.innerHTML;
};

watch(
  () => props.modelValue,
  (newValue) => {
    renderedHTML.value = injectScripts(newValue.value || "");
  },
  { immediate: true }
);
</script>

<template>
  <div class="w-full py-12 px-8 flex gap-x-10">
    <div v-html="renderedHTML"></div>
  </div>
</template>

