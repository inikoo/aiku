<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'

import { Editor, EditorContent } from '@tiptap/vue-3'
import { Editor as EditorType } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import TextStyle from '@tiptap/extension-text-style'
import FontSize from 'tiptap-extension-font-size'
import Underline from '@tiptap/extension-underline'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import CharacterCount from '@tiptap/extension-character-count'
import Highlight from '@tiptap/extension-highlight'
import { Color } from '@tiptap/extension-color'
import BulletList from '@tiptap/extension-bullet-list'
import ListItem from '@tiptap/extension-list-item'
import Link from '@tiptap/extension-link'

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faText, faUndoAlt, faRedoAlt } from '@far'
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faPaintBrushAlt, faTextHeight, faLink } from '@fal'
import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
library.add(faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, faPaintBrushAlt, faTextHeight, faLink, faText)

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        maxLength?: number
    }
}>()

const _editorInstance = ref<EditorType | null>(null)
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
    { slug: 'fontsize', icon: 'fal fa-text-height', active: 'fontsize', label: 'Font size' },
    { slug: 'link', icon: 'fal fa-link', active: 'link', label: 'Set link on text' },
]

// Comp: Characters count
const charactersCount = computed<number>(() => {
    return _editorInstance.value?.storage.characterCount.characters()
})

// Comp: Words count
const wordsCount = computed(() => {
    return _editorInstance.value?.storage.characterCount.words()
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

// Action: Bold, Italic, Undo, etc..
const onActionClick = (slug: string, option: string = '') => {
    const vm = _editorInstance.value?.chain().focus()
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
        // fontsize: () => vm?.setFontSize('36pt').run(),
        clear: () => {
            vm?.clearNodes().run()
            vm?.unsetAllMarks().run()
        },
        link: () => setLink()
        // textcolor: () => {
        //     vm?.setColor('#94FADB').run()
        // }
    }

    actionTriggers[slug]()
}

// Method: on click select Heading
const onHeadingClick = (index: number) => {
    const vm = _editorInstance.value?.chain().focus()
    vm?.toggleHeading({ level: index }).run()
}

onMounted(() => {
    _editorInstance.value = new Editor({
        content: props.form[props.fieldName],
        extensions: [
            StarterKit,
            Underline,
            Subscript,
            Superscript,
            TextStyle,
            BulletList,
            ListItem,
            CharacterCount.configure({
                limit: props.fieldData?.maxLength,
            }),
            Color.configure({
                types: ['textStyle'],
            }),
            FontSize.configure({
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
            Link.configure({
                openOnClick: false,
            }),
        ],
        onUpdate: () => {
            // console.log('qq', _editorInstance.value.getAttributes('textStyle'))
            // emits('update:modelValue', _editorInstance.value?.getHTML())
            props.form[props.fieldName] = _editorInstance.value?.getHTML()
        },
    })
})

onBeforeUnmount(() => {
    _editorInstance.value?.destroy()
})

const setLink = () => {
    const previousUrl = _editorInstance.value.getAttributes('link').href
    const url = window.prompt('URL', previousUrl)

    // cancelled
    if (url === null) {
    return
    }

    // empty
    if (url === '') {
    _editorInstance.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .unsetLink()
        .run()

    return
    }

    // update link
    _editorInstance.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: url })
        .run()
}

</script>

<template>
    <div id="text-editor" class="w-full border border-gray-400 rounded">
        <div class="p-2 flex items-center gap-x-1 gap-y-1.5 flex-wrap border-b border-gray-500" v-if="_editorInstance">
            <!-- Action: Heading -->
            <div class="group relative inline-block">
                <div class="text-xs min-w-16 p-1 appearance-none rounded cursor-pointer border border-gray-500"
                    :class="{'bg-slate-700 text-white font-bold': _editorInstance.isActive('heading')}"
                >
                    Heading <span id="headingIndex"></span>
                </div>
                <div class="cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                    <div v-for="index in 6"
                        class="block py-1.5 px-3 text-center cursor-pointer hover:bg-gray-300"
                        :class="{ 'bg-slate-700 text-white hover:bg-slate-700': _editorInstance.isActive('heading', { level: index }) }"
                        :style="{ fontSize: (20 - index) + 'px' }" @click="onHeadingClick(index)" role="button">
                        <Teleport v-if="_editorInstance.isActive('heading', { level: index })" to="#headingIndex">{{ index }}</Teleport>
                        H{{ index }}
                    </div>
                </div>
            </div>

            <template v-for="action in textActions" :key="action.slug">
                <!-- Action: color text -->
                <ColorPicker v-if="action.slug === 'textcolor'"
                    :color="_editorInstance?.getAttributes('textStyle').color"
                    @changeColor="(color) => _editorInstance?.chain().setColor(color.hex).run()"
                    class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700"

                >
                    <FontAwesomeIcon icon='far fa-text' fixed-width aria-hidden='true' :style="{color: _editorInstance?.getAttributes('textStyle').color || '#010101'}" />
                </ColorPicker>

                <!-- Action: color highlight -->
                <ColorPicker
                    v-else-if="action.slug === 'highlightcolor'"
                    :color="_editorInstance?.getAttributes('highlight').color"
                    @changeColor="(color) => _editorInstance?.chain().setHighlight({ color: color.hex }).run()"
                    class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700"
                    :style="{backgroundColor: _editorInstance?.getAttributes('highlight').color}"
                >
                    <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-gray-500' fixed-width aria-hidden='true' />
                </ColorPicker>

                <!-- Action: font size -->
                <div v-else-if="action.slug === 'fontsize'" class="group relative inline-block">
                    <div class="flex items-center text-xs min-w-10 py-1 pl-1.5 pr-0 appearance-none rounded cursor-pointer border border-gray-500"
                        :class="{'bg-slate-700 text-white font-bold': _editorInstance?.getAttributes('textStyle').fontSize}"
                    >
                        <div id="tiptapfontsize" class="pr-1.5">
                            <span class="hidden last:inline">Text size</span>
                        </div>
                        <div v-if="_editorInstance?.getAttributes('textStyle').fontSize" @click="_editorInstance?.chain().focus().unsetFontSize().run()" class="px-1">
                            <FontAwesomeIcon icon='fal fa-times' class='' fixed-width aria-hidden='true' />
                        </div>
                    </div>
                    <div class="w-min cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                        <div v-for="fontsize in ['8', '9', '12', '14', '16', '20', '24', '28', '36', '44', '52', '64']"
                            class="w-full block py-1.5 px-3 leading-none text-left cursor-pointer hover:bg-gray-300"
                            :class="{ 'bg-slate-700 text-white hover:bg-slate-700': parseInt(_editorInstance?.getAttributes('textStyle').fontSize, 10) == fontsize }"
                            :style="{ fontSize: fontsize + 'px'}"
                            @click="_editorInstance?.chain().focus().setFontSize(fontsize+'px').run()" role="button">
                            <Teleport v-if="parseInt(_editorInstance?.getAttributes('textStyle').fontSize, 10) == fontsize" to="#tiptapfontsize"><span>{{ fontsize }}</span></Teleport>
                            {{ fontsize }}
                        </div>
                    </div>
                </div>

                <!-- Action: bold, italic, align, etc -->
                <div v-else
                    v-tooltip="action.label"
                    @click="onActionClick(action.slug, action.option)"
                    class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer hover:border hover:border-gray-700"
                    :class="[action.class, { 'bg-slate-700 text-white': _editorInstance.isActive(action.active) }]"
                >
                    <FontAwesomeIcon v-if="action.icon" :icon='action.icon' class='text-sm' fixed-width aria-hidden='true' />
                </div>
            </template>
        </div>

        <EditorContent :editor="_editorInstance" />

        <!-- Counter: Characters and Words -->
        <div v-if="_editorInstance && fieldData?.maxLength" class="text-gray-500 text-sm text-right p-1.5">
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

    a {
        color: #e3ae00;
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
}
</style>
