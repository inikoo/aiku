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
import Heading from '@tiptap/extension-heading'
import TextAlign from '@tiptap/extension-text-align'
import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
import Highlight from '@tiptap/extension-highlight'
import { Color } from '@tiptap/extension-color'
import FontSize from 'tiptap-extension-font-size'
import Link from '@tiptap/extension-link'

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faText, faUndoAlt, faRedoAlt } from '@far'
import { faHorizontalRule, faQuoteRight, faMarker } from '@fas'
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faPaintBrushAlt, faTextHeight, faLink } from '@fal'
library.add(faBold, faQuoteRight, faMarker, faHorizontalRule, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, faPaintBrushAlt, faTextHeight, faLink, faText)

const props = defineProps({
    modelValue: String,
})

const emit = defineEmits(['update:modelValue'])

const onHeadingClick = (index) => {
    editor.value.chain().focus().toggleHeading({ level: index }).run()
}

const setLink = () => {
    const previousUrl = editor.value.getAttributes('link').href
    const url = window.prompt('URL', previousUrl)

    // cancelled
    if (url === null) {
        return
    }

    // empty
    if (url === '') {
        editor.value
            .chain()
            .focus()
            .extendMarkRange('link')
            .unsetLink()
            .run()

        return
    }

    // update link
    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: url })
        .run()
}


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
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Heading.configure({
            levels: [1, 2, 3],
        }),
        Highlight.configure({
            HTMLAttributes: {
                class: 'highlight-prosemirror',
            },
            multicolor: true
        }),
        Color.configure({
            types: ['textStyle'],
        }),
        FontSize.configure({
            types: ['textStyle'],
        }),
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
                    :class="{ 'bg-slate-700 text-white font-bold': editor.isActive('heading') }">
                    Heading <span id="headingIndex"></span>
                </div>
                <div
                    class="cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                    <div v-for="index in 6" class="block py-1.5 px-3 text-center cursor-pointer hover:bg-gray-300"
                        :class="{ 'bg-slate-700 text-white hover:bg-slate-700': editor.isActive('heading', { level: index }) }"
                        :style="{ fontSize: (20 - index) + 'px' }" role="button" @click="onHeadingClick(index)">
                        <div v-if="editor.isActive('heading', { level: index })" to="#headingIndex">{{ index }}</div>
                        H{{ index }}
                    </div>
                </div>
            </div>

            <div class="group relative inline-block">
                <div class="flex items-center text-xs min-w-10 py-1 pl-1.5 pr-0 appearance-none rounded cursor-pointer border border-gray-500"
                    :class="{ 'bg-slate-700 text-white font-bold': editor?.getAttributes('textStyle').fontSize }">
                    <div id="tiptapfontsize" class="pr-1.5">
                        <span class="hidden last:inline">Text size</span>
                    </div>
                    <div v-if="editor?.getAttributes('textStyle').fontSize"
                        @click="editor?.chain().focus().unsetFontSize().run()" class="px-1">
                        <FontAwesomeIcon icon='fal fa-times' class='' fixed-width aria-hidden='true' />
                    </div>
                </div>
                <div
                    class="w-min cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                    <div v-for="fontsize in ['8', '9', '12', '14', '16', '20', '24', '28', '36', '44', '52', '64']"
                        class="w-full block py-1.5 px-3 leading-none text-left cursor-pointer hover:bg-gray-300"
                        :class="{ 'bg-slate-700 text-white hover:bg-slate-700': parseInt(_editorInstance?.getAttributes('textStyle').fontSize, 10) == fontsize }"
                        :style="{ fontSize: fontsize + 'px' }"
                        @click="editor?.chain().focus().setFontSize(fontsize + 'px').run()" role="button">
                        <div v-if="parseInt(_editorInstance?.getAttributes('textStyle').fontSize, 10) == fontsize"
                            to="#tiptapfontsize"><span>{{ fontsize }}</span></div>
                        {{ fontsize }}
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
            <button type="button" @click="editor.chain().focus().setHorizontalRule().run()" class="p-1">
                <font-awesome-icon :icon="['fas', 'horizontal-rule']" />
            </button>
            <button type="button" @click="editor.chain().focus().setTextAlign('left').run()"
                class="p-1 disabled:text-gray-400">
                <FontAwesomeIcon icon='fal fa-align-left' />
            </button>
            <button type="button" @click="editor.chain().focus().setTextAlign('center').run()"
                class="p-1 disabled:text-gray-400">
                <FontAwesomeIcon icon='fal fa-align-center' />
            </button>
            <button type="button" @click="editor.chain().focus().setTextAlign('right').run()">
                <FontAwesomeIcon icon='fal fa-align-right' />
            </button>

            <button type="button" @click="editor.chain().focus().setTextAlign('right').run()">
                <FontAwesomeIcon icon='fal fa-align-right' />
            </button>

            <ColorPicker :color="editor?.getAttributes('highlight').color"
                @changeColor="(color) => editor?.chain().setHighlight({ color: color.hex }).run()"
                class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700"
                :style="{ backgroundColor: editor?.getAttributes('highlight').color }">
                <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-gray-500' fixed-width aria-hidden='true' />
            </ColorPicker>

            <ColorPicker :color="editor?.getAttributes('textStyle').color"
                @changeColor="(color) => editor?.chain().setColor(color.hex).run()"
                class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700">
                <FontAwesomeIcon icon='far fa-text' fixed-width aria-hidden='true'
                    :style="{ color: editor?.getAttributes('textStyle').color || '#010101' }" />
            </ColorPicker>


            <button type="button" @click="setLink">
                <FontAwesomeIcon icon='fal fa-link' />
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

    h1 {
        display: block;
        font-size: 2em;
        margin-block-start: 0.67em;
        margin-block-end: 0.67em;
        margin-inline-start: 0px;
        margin-inline-end: 0px;
        font-weight: bold;
        unicode-bidi: isolate;
    }

    h2 {
        display: block;
        font-size: 1.5em;
        margin-block-start: 0.83em;
        margin-block-end: 0.83em;
        margin-inline-start: 0px;
        margin-inline-end: 0px;
        font-weight: bold;
        unicode-bidi: isolate;
    }

    h3 {
        display: block;
        font-size: 1.17em;
        margin-block-start: 1em;
        margin-block-end: 1em;
        margin-inline-start: 0px;
        margin-inline-end: 0px;
        font-weight: bold;
        unicode-bidi: isolate;
    }

}
</style>