<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useEditor, EditorContent, BubbleMenu } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TextStyle from '@tiptap/extension-text-style'
import Underline from '@tiptap/extension-underline'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import BulletList from '@tiptap/extension-bullet-list'
import ListItem from '@tiptap/extension-list-item'
import Heading from '@tiptap/extension-heading'
import TextAlign from '@tiptap/extension-text-align'
import Highlight from '@tiptap/extension-highlight'
import { Color } from '@tiptap/extension-color'
import FontSize from 'tiptap-extension-font-size'
import Link from '@tiptap/extension-link'

import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faText, faUndoAlt, faRedoAlt } from '@far'
import { faHorizontalRule, faQuoteRight, faMarker } from '@fas'
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faPaintBrushAlt, faTextHeight, faLink } from '@fal'
library.add(faBold, faQuoteRight, faMarker, faHorizontalRule, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, faPaintBrushAlt, faTextHeight, faLink, faText)


const props = withDefaults(defineProps < {
    modelValue: string,
    toogle?: Array[]
} > (), {
    toogle: [
        'heading', 'fontSize', 'bold', 'italic', 'underline', 'bulletList',
        'orderedList', 'blockquote', 'divider', 'alignLeft', 'alignRight',
        'alignCenter', 'link', 'undo', 'redo', 'highlight', 'color', 'clear'
    ]
});

const emit = defineEmits(['update:modelValue'])

const toggleList = ref([
    { key: 'heading', action: () => onActionClick('heading') },
    { key: 'fontSize', action: () => onActionClick('fontSize') },
    { key: 'bold', icon: 'fal fa-bold', action: () => onActionClick('bold') },
    { key: 'italic', icon: 'fal fa-italic', action: () => onActionClick('italic') },
    { key: 'underline', icon: 'fal fa-underline', action: () => onActionClick('underline') },
    { key: 'bulletList', icon: 'fal fa-list-ul', action: () => onActionClick('bulletList') },
    { key: 'orderedList', icon: 'fal fa-list-ol', action: () => onActionClick('orderedList') },
    { key: 'blockquote', icon: 'fas fa-quote-right', action: () => onActionClick('blockquote') },
    { key: 'divider', icon: 'fas fa-horizontal-rule', action: () => onActionClick('divider') },
    { key: 'alignLeft', icon: 'fal fa-align-left', action: () => onActionClick('alignLeft') },
    { key: 'alignRight', icon: 'fal fa-align-right', action: () => onActionClick('alignRight') },
    { key: 'alignCenter', icon: 'fal fa-align-center', action: () => onActionClick('alignCenter') },
    { key: 'link', icon: 'fal fa-link', action: () => onActionClick('link') },
    { key: 'undo', icon: 'far fa-undo-alt', action: () => onActionClick('undo') },
    { key: 'redo', icon: 'far fa-redo-alt', action: () => onActionClick('redo') },
    { key: 'highlight', icon: 'fal fa-paint-brush-alt', action: () => onActionClick('highlight') },
    { key: 'color', icon: 'far fa-text', action: () => onActionClick('color') },
    { key: 'clear', icon: 'fal fa-eraser', active: 'clear', action: () => onActionClick('clear')  },
]);

const editor = useEditor({
    content: props.modelValue,
    onUpdate: ({ editor }) => {
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
            multicolor: true
        }),
        Color.configure({
            types: ['textStyle'],
        }),
        FontSize.configure({
            types: ['textStyle'],
        }),
        Link,
    ],
    editorProps: {
        attributes: {
            class: 'p-4 overflow-y-auto outline-none prose max-w-none',
        },
    },
});

const onActionClick = (key: string, option: string = '') => {
    if (!editor.value) return;

    const chain = editor.value.chain().focus();
    switch (key) {
        case 'bold':
            chain.toggleBold().run();
            break;
        case 'italic':
            chain.toggleItalic().run();
            break;
        case 'underline':
            chain.toggleUnderline().run();
            break;
        case 'bulletList':
            chain.toggleBulletList().run();
            break;
        case 'orderedList':
            chain.toggleOrderedList().run();
            break;
        case 'blockquote':
            chain.toggleBlockquote().run();
            break;
        case 'divider':
            chain.setHorizontalRule().run();
            break;
        case 'alignLeft':
            chain.setTextAlign('left').run();
            break;
        case 'alignRight':
            chain.setTextAlign('right').run();
            break;
        case 'alignCenter':
            chain.setTextAlign('center').run();
            break;
        case 'link':
            setLink();
            break;
        case 'undo':
            chain.undo().run();
            break;
        case 'redo':
            chain.redo().run();
            break;
        case 'clear':
            chain?.clearNodes().run()
            chain?.unsetAllMarks().run()
            break;
        default:
            console.warn(`Action not found for key: ${key}`);
            break;
    }
}

const onHeadingClick = (index: number) => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleHeading({ level: index }).run()
}

const setLink = () => {
    const previousUrl = editor.value?.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl) || '';

    if (url) {
        editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    } else {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    }
}

onMounted(() => {
    toggleList.value = toggleList.value.filter(item => props.toogle?.includes(item.key));
});

</script>

<template>
    <div>
        <BubbleMenu :editor="editor" :tippy-options="{ duration: 100 }" v-if="editor">
            <section
                class="buttons text-gray-700 flex items-center flex-wrap gap-x-4 border-t border-l border-r border-gray-400 p-1  bg-gray-200">

                <template v-for="action in toggleList" :key="action.key">

                    <div v-if="action.key == 'heading'" class="group relative inline-block">
                        <div class="text-xs min-w-16 p-1 appearance-none rounded cursor-pointer border border-gray-200"
                            :class="{ 'bg-slate-700 text-white font-bold': editor.isActive('heading') }">
                            Heading <span id="headingIndex"></span>
                        </div>
                        <div
                            class="cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                            <div v-for="index in 6"
                                class="block py-1.5 px-3 text-center cursor-pointer hover:bg-gray-300"
                                :class="{ 'bg-slate-700 text-white hover:bg-slate-700': editor.isActive('heading', { level: index }) }"
                                :style="{ fontSize: (20 - index) + 'px' }" role="button" @click="onHeadingClick(index)">
                                H{{ index }}
                            </div>
                        </div>
                    </div>



                    <div v-else-if="action.key == 'fontSize'" class="group relative inline-block">
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
                                :class="{ 'bg-slate-700 text-white hover:bg-slate-700': parseInt(editor?.getAttributes('textStyle').fontSize, 10) == fontsize }"
                                @click="editor?.chain().focus().setFontSize(fontsize + 'px').run()" role="button">
                                <div v-if="parseInt(editor?.getAttributes('textStyle').fontSize, 10) == fontsize"
                                    to="#tiptapfontsize"></div>
                                {{ fontsize }}
                            </div>
                        </div>
                    </div>


                    <ColorPicker v-else-if="action.key == 'highlight'" :color="editor?.getAttributes('highlight').color"
                        @changeColor="(color) => editor?.chain().setHighlight({ color: color.hex }).run()"
                        class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700"
                        :style="{ backgroundColor: editor?.getAttributes('highlight').color }">
                        <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-gray-500' fixed-width
                            aria-hidden='true' />
                    </ColorPicker>


                    <ColorPicker v-else-if="action.key == 'color'" :color="editor?.getAttributes('textStyle').color"
                        @changeColor="(color) => editor?.chain().setColor(color.hex).run()"
                        class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700">
                        <FontAwesomeIcon icon='far fa-text' fixed-width aria-hidden='true'
                            :style="{ color: editor?.getAttributes('textStyle').color || '#010101' }" />
                    </ColorPicker>



                    <button v-else type="button" @click="action?.action"
                        :class="{ 'bg-gray-200 rounded': editor.isActive(action?.active) }" class="p-1">
                        <span v-if="action.icon">
                            <FontAwesomeIcon :icon='action.icon' />
                        </span>
                    </button>


                </template>

            </section>
        </BubbleMenu>
        
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