<script setup lang="ts">
import { faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter } from "@fad";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faLink, faUnlink } from "@fal";
import { faExclamation, faCaretDown, faCaretLeft } from "@fas";

import { ref, watch } from "vue";
import PureInputNumber from "@/Components/Pure/PureInputNumber.vue";

library.add(faExclamation, faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter, faLink, faUnlink, faCaretDown, faCaretLeft);

const emit = defineEmits(['update:modelValue']);
// Reactive model with example data
const model = defineModel()
const value = ref({
    width: parseInt(model.value.width),
    height: parseInt(model.value.height),
    position: {
        top: parseInt(model.value.position.top),
        left: parseInt(model.value.position.left),
        right: parseInt(model.value.position.right),
        bottom: parseInt(model.value.position.bottom)
    }
})

watch(
  value,
  (newValue) => {
    model.value = {
      width: `${newValue.width}px`,
      height: `${newValue.height}px`,
      position: {
        top: `${newValue.position.top}px`,
        left: `${newValue.position.left}px`,
        right: `${newValue.position.right}px`,
        bottom: `${newValue.position.bottom}px`,
      },
    };
  },
  { deep: true }
);


</script>

<template>
    <div class="w-full my-2 text-start pb-1 font-semibold select-none text-sm border-b border-gray-300">Width</div>
    <PureInputNumber v-model="value.width" suffix="px"></PureInputNumber>

    <div class="w-full my-2 text-start pb-1 font-semibold select-none text-sm border-b border-gray-300">Height</div>
    <PureInputNumber v-model="value.height" suffix="px"></PureInputNumber>

    <div class="w-full my-2 text-start pb-1 font-semibold select-none text-sm border-b border-gray-300">Top Position
    </div>
    <PureInputNumber v-model="value.position.top" suffix="px"></PureInputNumber>

    <div class="w-full my-2 text-start pb-1 font-semibold select-none text-sm border-b border-gray-300">Left Position
    </div>
    <PureInputNumber v-model="value.position.left" suffix="px"></PureInputNumber>

    <div class="w-full my-2 text-start pb-1 font-semibold select-none text-sm border-b border-gray-300">Right Position
    </div>
    <PureInputNumber v-model="value.position.right" suffix="px"></PureInputNumber>

    <div class="w-full my-2 text-start pb-1 font-semibold select-none text-sm border-b border-gray-300">Bottom Position
    </div>
    <PureInputNumber v-model="value.position.bottom" suffix="px"></PureInputNumber>
</template>

<style scoped>
/* Add custom styles if necessary */
</style>
