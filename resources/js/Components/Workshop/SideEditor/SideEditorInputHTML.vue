<script setup lang="ts">
import Editor from '@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue'
import { EditorContent } from '@tiptap/vue-3'

const props = defineProps<{}>()
const model = defineModel<{ text: string | null }>()
console.log(model.value)
</script>

<template>
  <div class="py-3">
    <Editor v-model="model">
      <template #editor-content="{ editor }">
        <div class="editor-wrapper border-2 border-gray-300 rounded-lg p-3 shadow-sm focus-within:border-blue-400">
          <EditorContent :editor="editor" class="editor-content focus:outline-none" />
        </div>
      </template>
    </Editor>
  </div>
</template>

<style scoped>
.editor-wrapper {
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.editor-content {
  min-height: 150px;
  font-size: 1rem;
  line-height: 1.5;
}
</style>
