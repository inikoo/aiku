<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue'

import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import Underline from '@tiptap/extension-underline'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import CharacterCount from '@tiptap/extension-character-count'

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, } from '@fal'
library.add(faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt)

const editor = ref(false)

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        maxLength?: number
    }
}>()

const editorInstance = ref(null)
const textActions = [
    { slug: 'bold', icon: 'fal fa-bold', active: 'bold', label: 'Bold' },
    { slug: 'italic', icon: 'fal fa-italic', active: 'italic', label: 'Italic' },
    { slug: 'underline', icon: 'fal fa-underline', active: 'underline', label: 'Underline' },
    { slug: 'strike', icon: 'fal fa-strikethrough', active: 'strike', label: 'Strikethrough' },
    { slug: 'align', option: 'left', icon: 'fal fa-align-left', active: { textAlign: 'left' }, label: 'Align left' },
    { slug: 'align', option: 'center', icon: 'fal fa-align-center', active: { textAlign: 'center' }, label: 'Align center' },
    { slug: 'align', option: 'right', icon: 'fal fa-align-right', active: { textAlign: 'right' }, label: 'Align right' },
    { slug: 'align', option: 'justify', icon: 'fal fa-align-justify', active: { textAlign: 'justify' }, label: 'Justify' },
    { slug: 'bulletList', icon: 'fal fa-list-ul', active: 'bulletList', label: 'Unordered list' },
    { slug: 'orderedList', icon: 'fal fa-list-ol', active: 'orderedList', label: 'Ordered list' },
    { slug: 'subscript', icon: 'fal fa-subscript', active: 'subscript', label: 'Subscript' },
    { slug: 'superscript', icon: 'fal fa-superscript', active: 'superscript', label: 'Superscript' },
    { slug: 'undo', icon: 'fal fa-undo-alt', active: 'undo', label: 'Undo' },
    { slug: 'redo', icon: 'fal fa-redo-alt', active: 'redo', label: 'Redo' },
    { slug: 'clear', icon: 'fal fa-eraser', active: 'clear', label: 'Clear style on selected text', class: 'text-red-500' },
]

const charactersCount = computed<number>(() => {
    return editorInstance.value?.storage.characterCount.characters()
})

const wordsCount = computed(() => {
    return editorInstance.value.storage.characterCount.words()
})

const limitWarning = computed(() => {
    if (!props.fieldData?.maxLength) return ''

    const isCloseToMax = (charactersCount.value >= (props.fieldData?.maxLength - 20))
    const isMax = charactersCount.value === props.fieldData?.maxLength

    if (isCloseToMax && !isMax) return 'text-yellow-500'
    if (isMax) return 'text-red-500'

    return ''
})


// watch(() => props.modelValue, (newVal) => {
//     if (editorInstance.value.getHTML() === newVal) return
//     editorInstance.value.commands.setContent(props.modelValue, false)
// })

// Action: Bold, Italic, Undo, etc..
const onActionClick = (slug: string, option: string | null = null) => {
    const vm = editorInstance.value.chain().focus()
    const actionTriggers: { [key: string]: Function } = {
        bold: () => vm.toggleBold().run(),
        italic: () => vm.toggleItalic().run(),
        underline: () => vm.toggleUnderline().run(),
        strike: () => vm.toggleStrike().run(),
        bulletList: () => vm.toggleBulletList().run(),
        orderedList: () => vm.toggleOrderedList().run(),
        align: () => vm.setTextAlign(option).run(),
        subscript: () => vm.toggleSubscript().run(),
        superscript: () => vm.toggleSuperscript().run(),
        undo: () => vm.undo().run(),
        redo: () => vm.redo().run(),
        clear: () => {
            vm.clearNodes().run()
            vm.unsetAllMarks().run()
        },
    }

    actionTriggers[slug]()
}

// Method: on click select Heading
const onHeadingClick = (index: number) => {
    const vm = editorInstance.value.chain().focus()
    vm.toggleHeading({ level: index }).run()
}

onMounted(() => {
    editorInstance.value = new Editor({
        content: props.form[props.fieldName],
        extensions: [
            StarterKit,
            Underline,
            Subscript,
            Superscript,
            CharacterCount.configure({
                limit: props.fieldData?.maxLength,
            }),
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
        ],
        onUpdate: () => {
            // emits('update:modelValue', editorInstance.value.getHTML())
            props.form[props.fieldName] = editorInstance.value.getHTML()
        },
    })
})

onBeforeUnmount(() => {
    editorInstance.value.destroy()
})

</script>

<template>
    <div id="text-editor" class="w-full border border-gray-400 rounded">
        <div class="px-1 py-2 flex items-center gap-x-1 gap-y-1.5 flex-wrap border-b border-gray-500" v-if="editorInstance">
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

            <div v-for="action in textActions"
                v-tooltip="action.label"
                @click="onActionClick(action.slug, action.option)"
                class="appearance-none flex items-center justify-center w-6 aspect-square rounded cursor-pointer hover:border hover:border-gray-700"
                :class="[action.class, { 'bg-slate-700 text-white': editorInstance.isActive(action.active) }]"
            >
                <FontAwesomeIcon :icon='action.icon' class='text-sm' fixed-width aria-hidden='true' />
            </div>
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
