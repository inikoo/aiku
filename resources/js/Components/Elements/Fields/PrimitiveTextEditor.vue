<template>
  <div class="">

    <div class="bg-white rounded border border-gray-400 focus:border-gray-300">
      <!-- Button Group -->
      <div class="bg-indigo-100 p-2 grid grid-flow-col space-around text-slate-800 select-none space-x-1 border border-gray-100">
        <div class="grid grid-flow-col justify-start space-x-1">
          <div :class="{ 'bg-indigo-400 text-white': editor.isActive('bold') }"
            class="rounded-sm grid justify-center items-center  border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
            @click="editor.chain().focus().toggleBold().run()">
            <FontAwesomeIcon aria-hidden="true" icon="far fa-bold" />
          </div>
          <div :class="{ 'bg-indigo-400 text-white': editor.isActive('italic') }"
            class="rounded-sm grid justify-center items-center  border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
            @click="editor.chain().focus().toggleItalic().run()">
            <FontAwesomeIcon aria-hidden="true" icon="far fa-italic" />
          </div>
          <div :class="{ 'bg-indigo-400 text-white': editor.isActive('underline') }"
            class="rounded-sm grid justify-center items-end  border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
            @click="editor.chain().focus().toggleUnderline().run()">
            <FontAwesomeIcon aria-hidden="true" icon="far fa-underline" />
          </div>
          <div :class="{ 'bg-indigo-400 text-white': editor.isActive('bulletList') }"
            class="rounded-sm grid justify-center items-center  border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
            @click="editor.chain().focus().toggleBulletList().run()">
            <FontAwesomeIcon aria-hidden="true" icon="far fa-list-ul" />
          </div>
          <div :class="{ 'bg-indigo-400 text-white': editor.isActive('orderedList') }"
            class="rounded-sm grid justify-center items-center  border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
            @click="editor.chain().focus().toggleOrderedList().run()">
            <FontAwesomeIcon aria-hidden="true" icon="far fa-list-ol" />
          </div>
          <div :class="{ 'bg-indigo-400 text-white': editor.isActive('orderedList') }"
            class="rounded-sm grid justify-center items-center  border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
            @click="editor.commands.undo()">
            <FontAwesomeIcon aria-hidden="true" icon="far fa-undo" />
          </div>
          <div :class="{ 'bg-indigo-400 text-white': editor.isActive('orderedList') }"
            class="rounded-sm grid justify-center items-center  border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
            @click="editor.commands.redo()">
            <FontAwesomeIcon aria-hidden="true" icon="far fa-redo" />
          </div>
        </div>
        <div
          class="w-min rounded-sm grid justify-end items-center place-self-end border border-transparent active:border-indigo-700 box-content cursor-pointer px-1 py-0.5"
          @click="editor.chain().focus().clearContent(true).run()">
          <FontAwesomeIcon aria-hidden="true" icon="far fa-trash-alt" />
        </div>
      </div>
      <!-- The Editor -->
      <EditorContent :editor="editor" />
    </div>

    <div v-if="props.showStats" class="grid grid-flow-col text-xs italic text-gray-500 mt-2 space-x-12 justify-start">
      <p class="">
        <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
        Letters: {{ editor.storage.characterCount.characters() }}
      </p>
      <p class="">
        <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
        Words: {{ editor.storage.characterCount.words() }}
      </p>
    </div>
  </div>
</template>
  
<script setup>
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Underline from '@tiptap/extension-underline'
import CharacterCount from '@tiptap/extension-character-count'
import { Editor, EditorContent } from '@tiptap/vue-3';
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

import { library } from "@fortawesome/fontawesome-svg-core"
import { faBold, faItalic, faUnderline, faTrashAlt, faListUl, faListOl, faUndo, faRedo } from "@/../private/pro-regular-svg-icons"
library.add(faBold, faItalic, faUnderline, faTrashAlt, faListUl, faListOl, faUndo, faRedo)
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

const props = defineProps(['modelValue', 'showStats', 'placeholder']);
const emit = defineEmits(['update:modelValue']);
const editor = ref(false)

watch(
  () => props.modelValue,
  (value) => {
    const isSame = editor.value.getHTML() === value

    if (isSame) {
      return
    }

    editor.commands.setContent(value, false)
  }
)

editor.value = new Editor({
  editorProps: {
    attributes: {
      class: 'focus:outline-none',
    },
  },
  extensions: [
    StarterKit,
    CharacterCount.configure({
      limit: null,
    }),
    Placeholder.configure({
      placeholder: props.placeholder,
    }),
    Underline,
  ],
  content: props.modelValue,
  onUpdate: () => {
    emit('update:modelValue', editor.value.getHTML())
  }
})

onBeforeUnmount(() => {
  editor.value.destroy()
})

</script>
  
<style>
.ProseMirror {
  padding: 7px 15px;
}

.ProseMirror p {
  width: 400px;
  /* background: #e1e1e1; */
  /* max-width: 100%; */
  word-wrap: break-word;
  outline-color: #6b7280 !important;
  display: inline-block;
}


.ProseMirror ul,
ol {
  display: block;
  padding-left: 30px;
  list-style-position: outside;
}

.ProseMirror ul {
  list-style-type: decimal;
}

.ProseMirror ol {
  list-style-type: disc;
}

.ProseMirror p.is-editor-empty:first-child::before {
  color: #adb5bd;
  font-size: small;
  font-style: italic;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

</style>