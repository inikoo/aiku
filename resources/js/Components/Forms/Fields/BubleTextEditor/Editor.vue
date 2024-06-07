<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TextStyle from '@tiptap/extension-text-style'
import Underline from '@tiptap/extension-underline'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import BulletList from '@tiptap/extension-bullet-list'
import ListItem from '@tiptap/extension-list-item'

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faText, faUndoAlt, faRedoAlt } from '@far'
import { faHorizontalRule, faQuoteRight } from '@fas'
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faPaintBrushAlt, faTextHeight, faLink } from '@fal'
library.add(faBold, faQuoteRight, faHorizontalRule, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, faPaintBrushAlt, faTextHeight, faLink, faText)

const props = defineProps({
    modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

/* const onHeadingClick = (index) => {
    editor.value.chain().focus().toggleHeading({ level: index }).run()
}
 */
const editor = useEditor({
    content: props.modelValue,
    onUpdate: ({ editor }) => {
        // console.log(editor.getHTML())
        emit('update:modelValue', editor.getHTML())
    },
    extensions: [
        StarterKit,
        Underline,
        Subscript,
        Superscript,
        TextStyle,
        BulletList,
        ListItem,
    ],
    editorProps: {
        attributes: {
            class:
                'border-t border-gray-400 p-4  overflow-y-auto outline-none prose max-w-none',
        },
    },
})
</script>

<template>
    <div>
        <section v-if="editor"
            class="buttons text-gray-700 flex items-center flex-wrap gap-x-4 border-t border-l border-r border-gray-400 p-1">

            <div class="group relative inline-block">
                <div class="text-xs min-w-16 p-1 appearance-none rounded cursor-pointer border border-gray-200"
                    :class="{'bg-slate-700 text-white font-bold': editor.isActive('heading')}"
                >
                    Heading <span id="headingIndex"></span>
                </div>
                <div class="cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                    <div v-for="index in 6"
                        class="block py-1.5 px-3 text-center cursor-pointer hover:bg-gray-300"
                        :class="{ 'bg-slate-700 text-white hover:bg-slate-700': editor.isActive('heading', { level: index }) }"
                        :style="{ fontSize: (20 - index) + 'px' }"  role="button">
                      <!--   <Teleport v-if="editor.isActive('heading', { level: index })" to="#headingIndex">{{ index }}</Teleport> -->
                        H{{ index }}
                    </div>
                </div>
            </div>

            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-gray-200 rounded': editor.isActive('bold') }" class="p-1">
                <FontAwesomeIcon icon='fal fa-bold' />

            </button>
            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-gray-200 rounded': editor.isActive('italic') }" class="p-1">
                <FontAwesomeIcon icon='fal fa-italic' />
            </button>
            <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
                :class="{ 'bg-gray-200 rounded': editor.isActive('underline') }" class="p-1">
                <FontAwesomeIcon icon='fal fa-underline' />
            </button>
            <!--    <button type="button" @click="editor.chain().focus().toggleHeading({ level: 1 }).run()" :class="{
                'bg-gray-200 rounded': editor.isActive('heading', { level: 1 }),
            }" class="p-1">
                <FontAwesomeIcon icon='far fa-text' />
            </button>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="{
                'bg-gray-200 rounded': editor.isActive('heading', { level: 2 }),
            }" class="p-1">
                <FontAwesomeIcon icon='far fa-text' />
            </button> -->
            <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-gray-200 rounded': editor.isActive('bulletList') }" class="p-1">
                <FontAwesomeIcon icon='fal fa-list-ul' />
            </button>
            <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'bg-gray-200 rounded': editor.isActive('orderedList') }" class="p-1">
                <FontAwesomeIcon icon='fal fa-list-ol' />
            </button>
            <button type="button" @click="editor.chain().focus().setBlockquote().run()"
                :class="{ 'bg-gray-200 rounded': editor.isActive('blockquote') }" class="p-1">
                <font-awesome-icon :icon="['fas', 'quote-right']" />
            </button>
            <!-- <button type="button" @click="editor.chain().focus().toggleCode().run()"
                :class="{ 'bg-gray-200 rounded': editor.isActive('code') }" class="p-1">
                <FontAwesomeIcon icon='far fa-text' />
            </button> -->
            <button type="button" @click="editor.chain().focus().setHorizontalRule().run()" class="p-1">
                <font-awesome-icon :icon="['fas', 'horizontal-rule']" />
            </button>
            <button type="button" class="p-1 disabled:text-gray-400" @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().chain().focus().undo().run()">
                <FontAwesomeIcon icon='far fa-undo-alt' />
            </button>
            <button type="button" @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().chain().focus().redo().run()" class="p-1 disabled:text-gray-400">
                <FontAwesomeIcon icon='far fa-redo-alt' />
            </button>
        </section>
        <EditorContent :editor="editor" />
    </div>
</template>


<style lang="scss">
/* Basic editor styles */
.tiptap {
    >*+* {
        margin-top: 0.75em;
    }

    blockquote {
        padding-left: 1rem;
        border-left: 3px solid rgba(#0D0D0D, 0.1);
    }


    ul,
    ol {
        padding: 0 1rem;
    }

    ul {
        list-style: disc
    }

    ol {
        list-style: decimal
    }

    .ProseMirror {
        height: fit-content;
        width: 100%;
        overflow-y: auto;
        padding-left: 0.5em;
        padding-right: 0.5em;
        outline: none;
    }

}
</style>