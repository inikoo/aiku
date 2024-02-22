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
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faTrashAlt, faListUl, faListOl, faUndoAlt, faRedoAlt, } from '@fal'
library.add(faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faTrashAlt, faListUl, faListOl, faUndoAlt, faRedoAlt)

const editor = ref(false)

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        maxLength?: number
        maxLimit?: number
    }
}>()

const editorInstance = ref(null)
const textActions = [
    { slug: 'bold', icon: 'fal fa-bold', active: 'bold' },
    { slug: 'italic', icon: 'fal fa-italic', active: 'italic' },
    { slug: 'underline', icon: 'fal fa-underline', active: 'underline' },
    { slug: 'strike', icon: 'fal fa-strikethrough', active: 'strike' },
    { slug: 'align', option: 'left', icon: 'fal fa-align-left', active: { textAlign: 'left' } },
    { slug: 'align', option: 'center', icon: 'fal fa-align-center', active: { textAlign: 'center' } },
    { slug: 'align', option: 'right', icon: 'fal fa-align-right', active: { textAlign: 'right' } },
    { slug: 'align', option: 'justify', icon: 'fal fa-align-justify', active: { textAlign: 'justify' } },
    { slug: 'bulletList', icon: 'fal fa-list-ul', active: 'bulletList' },
    { slug: 'orderedList', icon: 'fal fa-list-ol', active: 'orderedList' },
    { slug: 'subscript', icon: 'fal fa-subscript', active: 'subscript' },
    { slug: 'superscript', icon: 'fal fa-superscript', active: 'superscript' },
    { slug: 'undo', icon: 'fal fa-undo-alt', active: 'undo' },
    { slug: 'redo', icon: 'fal fa-redo-alt', active: 'redo' },
    { slug: 'clear', icon: 'fal fa-trash-alt', active: 'clear' },
]

const charactersCount = computed<number>(() => {
    return editorInstance.value?.storage.characterCount.characters()
})

const wordsCount = computed(() => {
    return editorInstance.value.storage.characterCount.words()
})

const limitWarning = computed(() => {
    if (!props.fieldData?.maxLimit) return ''

    const isCloseToMax = (charactersCount.value >= (props.fieldData?.maxLimit - 20))
    const isMax = charactersCount.value === props.fieldData?.maxLimit

    if (isCloseToMax && !isMax) return 'text-orange-500'
    if (isMax) return 'text-red-500'

    return ''
})


// watch(() => props.modelValue, (newVal) => {
//     if (editorInstance.value.getHTML() === newVal) return
//     editorInstance.value.commands.setContent(props.modelValue, false)
// })


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
                limit: props.fieldData?.maxLimit,
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
    <div id="text-editor" class="qwezxc">
        <div class="px-1 py-2 flex items-center gap-x-1 flex-wrap border-b border-gray-500" v-if="editorInstance">
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

            <div v-for="{ slug, option, active, icon }, index in textActions"
                class="appearance-none flex items-center justify-center w-6 aspect-square rounded"
                :class="{ 'bg-slate-700 text-white': editorInstance.isActive(active) }" @click="onActionClick(slug, option)">
                <FontAwesomeIcon :icon='icon' class='text-sm' fixed-width aria-hidden='true' />
            </div>
        </div>

        <EditorContent :editor="editorInstance" />

        <div v-if="editor" class="text-gray-500 text-sm text-right p-1.5">
            <span :class="fieldData?.maxLimit ? limitWarning : ''">
                {{ charactersCount }} {{ fieldData?.maxLimit ? `/ ${fieldData?.maxLimit} characters` : 'characters' }}
            </span>
            |
            <span>
                {{ wordsCount }} words
            </span>
        </div>
    </div>

    {{ form[fieldName] }}fff
</template>

<style lang="scss">
#text-editor {


    .align-dropdown {
        position: relative;
        display: inline-block;
        margin: 0.5em 8px;


    }

    .ProseMirror {
        height: 300px;
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
