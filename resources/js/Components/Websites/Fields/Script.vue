<script setup lang="ts">
import { ref, shallowRef, watch } from "vue";
import { Codemirror } from "vue-codemirror";
import { javascript } from "@codemirror/lang-javascript";
import { css } from "@codemirror/lang-css";
import { html } from "@codemirror/lang-html";
import { EditorView } from "@codemirror/view";
import Button from "@/Components/Elements/Buttons/Button.vue";

// Define props and emit for two-way binding
const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
});
const emit = defineEmits(["update:modelValue"]);

// Reactive code state copied from modelValue
const code = ref(props.modelValue);

// CodeMirror extensions for JavaScript, CSS, and HTML
const extensions = [javascript(), css(), html()];

// EditorView instance reference
const view = shallowRef<EditorView>();

// Handle Codemirror ready event
const handleReady = (payload: { view: EditorView }) => {
  view.value = payload.view;
};

// Apply code and emit updated modelValue
const applyCode = () => {
  emit("update:modelValue", code.value);
};


</script>

<template>
  <div class="flex justify-end mb-3">
    <Button label="Apply" type="save" size="xs" @click="applyCode" />
  </div>

  <codemirror
    v-model="code"
    placeholder="Code goes here..."
    :style="{ height: '300px' }"
    :autofocus="true"
    :indent-with-tab="true"
    :tab-size="2"
    :extensions="extensions"
    @ready="handleReady"
  />
</template>

<style scoped>
/* Optional: Styling for the editor container */
.codemirror {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}
</style>
