<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'

import { Editor, EditorContent } from '@tiptap/vue-3'
import { Editor as EditorType } from '@tiptap/core/dist/packages/core/src/Editor.d.ts'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import TextStyle from '@tiptap/extension-text-style'
import Underline from '@tiptap/extension-underline'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import CharacterCount from '@tiptap/extension-character-count'
import Highlight from '@tiptap/extension-highlight'
import { Color } from '@tiptap/extension-color'

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faText, faUndoAlt, faRedoAlt } from '@far'
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faPaintBrushAlt } from '@fal'
import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
library.add(faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, faPaintBrushAlt, faText)

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        maxLength?: number
    }
}>()

const editorInstance = ref<EditorType | null>(null)
const textActions = [
    { slug: 'bold', icon: 'fal fa-bold', active: 'bold', label: 'Bold' },
    { slug: 'italic', icon: 'fal fa-italic', active: 'italic', label: 'Italic' },
    { slug: 'underline', icon: 'fal fa-underline', active: 'underline', label: 'Underline' },
    { slug: 'strike', icon: 'fal fa-strikethrough', active: 'strike', label: 'Strikethrough' },
    { slug: 'align', option: 'left', icon: 'fal fa-align-left', active: { textAlign: 'left' }, label: 'Align left' },
    { slug: 'align', option: 'center', icon: 'fal fa-align-center', active: { textAlign: 'center' }, label: 'Align center' },
    { slug: 'align', option: 'right', icon: 'fal fa-align-right', active: { textAlign: 'right' }, label: 'Align right' },
    { slug: 'align', option: 'justify', icon: 'fal fa-align-justify', active: { textAlign: 'justify' }, label: 'Justify' },
    { slug: 'textcolor', icon: 'far fa-text', active: 'textcolor', label: 'Text color' },
    { slug: 'highlightcolor', active: 'highlightcolor', label: 'Highlight color' },
    { slug: 'bulletList', icon: 'fal fa-list-ul', active: 'bulletList', label: 'Unordered list' },
    { slug: 'orderedList', icon: 'fal fa-list-ol', active: 'orderedList', label: 'Ordered list' },
    { slug: 'subscript', icon: 'fal fa-subscript', active: 'subscript', label: 'Subscript' },
    { slug: 'superscript', icon: 'fal fa-superscript', active: 'superscript', label: 'Superscript' },
    { slug: 'undo', icon: 'far fa-undo-alt', active: 'undo', label: 'Undo' },
    { slug: 'redo', icon: 'far fa-redo-alt', active: 'redo', label: 'Redo' },
    { slug: 'clear', icon: 'fal fa-eraser', active: 'clear', label: 'Clear style on selected text', class: 'text-red-500' },
]

// Comp: Characters count
const charactersCount = computed<number>(() => {
    return editorInstance.value?.storage.characterCount.characters()
})

// Comp: Words count
const wordsCount = computed(() => {
    return editorInstance.value?.storage.characterCount.words()
})

// Comp: Warning maxLength class
const limitWarning = computed(() => {
    if (!props.fieldData?.maxLength) return ''

    const isCloseToMax = (charactersCount.value >= (props.fieldData?.maxLength - 20))
    const isMax = charactersCount.value === props.fieldData?.maxLength

    if (isCloseToMax && !isMax) return 'text-yellow-500'
    if (isMax) return 'text-red-500'

    return ''
})


// watch(() => props.modelValue, (newVal) => {
//     if (editorInstance.value?.getHTML() === newVal) return
//     editorInstance.value?.commands.setContent(props.modelValue, false)
// })

// Action: Bold, Italic, Undo, etc..
const onActionClick = (slug: string, option: string = '') => {
    const vm = editorInstance.value?.chain().focus()
    const actionTriggers: { [key: string]: Function } = {
        bold: () => vm?.toggleBold().run(),
        italic: () => vm?.toggleItalic().run(),
        underline: () => vm?.toggleUnderline().run(),
        strike: () => vm?.toggleStrike().run(),
        bulletList: () => vm?.toggleBulletList().run(),
        orderedList: () => vm?.toggleOrderedList().run(),
        align: () => vm?.setTextAlign(option).run(),
        subscript: () => vm?.toggleSubscript().run(),
        superscript: () => vm?.toggleSuperscript().run(),
        undo: () => vm?.undo().run(),
        redo: () => vm?.redo().run(),
        clear: () => {
            vm?.clearNodes().run()
            vm?.unsetAllMarks().run()
        },
        // textcolor: () => {
        //     vm?.setColor('#94FADB').run()
        // }
    }

    actionTriggers[slug]()
}

// Method: on click select Heading
const onHeadingClick = (index: number) => {
    const vm = editorInstance.value?.chain().focus()
    vm?.toggleHeading({ level: index }).run()
}

onMounted(() => {
    editorInstance.value = new Editor({
        content: props.form[props.fieldName],
        extensions: [
            StarterKit,
            Underline,
            Subscript,
            Superscript,
            TextStyle,
            CharacterCount.configure({
                limit: props.fieldData?.maxLength,
            }),
            Color.configure({
                types: ['textStyle'],
            }),
            Highlight.configure({
                HTMLAttributes: {
                    class: 'highlight-prosemirror',
                },
                multicolor: true
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
        ],
        onUpdate: () => {
            // console.log('qq', editorInstance.value.getAttributes('textStyle'))
            // emits('update:modelValue', editorInstance.value?.getHTML())
            props.form[props.fieldName] = editorInstance.value?.getHTML()
        },
    })
})

onBeforeUnmount(() => {
    editorInstance.value?.destroy()
})

</script>

<template>
    <div id="text-editor" class="w-full border border-gray-400 rounded">
        <div class="p-2 flex items-center gap-x-1 gap-y-1.5 flex-wrap border-b border-gray-500" v-if="editorInstance">
            <div class="group relative inline-block">
                <div class="text-xs min-w-16 p-1 appearance-none rounded cursor-pointer border border-gray-500"
                    :class="{'bg-slate-700 text-white font-bold': editorInstance.isActive('heading')}"
                >
                    Heading <span id="headingIndex"></span>
                </div>
                <div class="cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                    <div v-for="index in 6"
                        class="block py-1.5 px-3 text-center cursor-pointer hover:bg-gray-300"
                        :class="{ 'bg-slate-700 text-white hover:bg-slate-700': editorInstance.isActive('heading', { level: index }) }"
                        :style="{ fontSize: (20 - index) + 'px' }" @click="onHeadingClick(index)" role="button">
                        <Teleport v-if="editorInstance.isActive('heading', { level: index })" to="#headingIndex">{{ index }}</Teleport>
                        H{{ index }}
                    </div>
                </div>
            </div>

            <template v-for="action in textActions" :key="action.slug">
                <!-- Color: Text -->
                <ColorPicker v-if="action.slug === 'textcolor'"
                    :color="editorInstance?.getAttributes('textStyle').color"
                    @changeColor="(color) => editorInstance?.chain().setColor(color.hex).run()"
                    class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700"

                >
                    <FontAwesomeIcon icon='far fa-text' fixed-width aria-hidden='true' :style="{color: editorInstance?.getAttributes('textStyle').color || '#010101'}" />
                </ColorPicker>

                <!-- Color: Highlight -->
                <ColorPicker
                    v-else-if="action.slug === 'highlightcolor'"
                    :color="editorInstance?.getAttributes('highlight').color"
                    @changeColor="(color) => editorInstance?.chain().setHighlight({ color: color.hex }).run()"
                    class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700"
                    :style="{backgroundColor: editorInstance?.getAttributes('highlight').color}"
                >
                    <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-gray-500' fixed-width aria-hidden='true' />
                </ColorPicker>
                    
                <div v-else
                    v-tooltip="action.label"
                    @click="onActionClick(action.slug, action.option)"
                    class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer hover:border hover:border-gray-700"
                    :class="[action.class, { 'bg-slate-700 text-white': editorInstance.isActive(action.active) }]"
                >
                    <FontAwesomeIcon :icon='action.icon' class='text-sm' fixed-width aria-hidden='true' />
                </div>
            </template>
        </div>

        <EditorContent :editor="editorInstance" />

        <!-- Counter: Characters and Words -->
        <div v-if="editorInstance && fieldData?.maxLength" class="text-gray-500 text-sm text-right p-1.5">
            <span :class="fieldData?.maxLength ? limitWarning : ''">
                {{ charactersCount }} {{ fieldData?.maxLength ? `/ ${fieldData?.maxLength} characters` : 'characters' }}
            </span>
            |
            <span>
                {{ wordsCount }} {{ wordsCount > 1 ? 'words' : 'word'}}
            </span>
        </div>
    </div>

    {{ form[fieldName] }}
</template>

<style lang="scss">
#text-editor {

    .highlight-prosemirror {
        @apply px-1 py-0.5
    }

    .ProseMirror {
        height: 300px;
        width: 100%;
        overflow-y: auto;
        padding-left: 0.5em;
        padding-right: 0.5em;
        outline: none;

        >p:first-child {
            margin-top: 0.5em;
        }

        >h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            &:first-child {
                margin-top: 0.5em;
            }
        }
    }
}
</style>
