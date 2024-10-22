<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue"
import { useEditor, EditorContent, BubbleMenu } from '@tiptap/vue-3'
/* import type DataTable from "@/models/table" */

import TiptapToolbarButton from "@/Components/Forms/Fields/BubleTextEditor/TiptapToolbarButton.vue"
import TiptapToolbarGroup from "@/Components/Forms/Fields/BubleTextEditor/TiptapToolbarGroup.vue"
import Paragraph from "@tiptap/extension-paragraph"
import Document from "@tiptap/extension-document"
import Text from "@tiptap/extension-text"
import History from "@tiptap/extension-history"
import Heading from "@tiptap/extension-heading"
import Bold from "@tiptap/extension-bold"
import Italic from "@tiptap/extension-italic"
import Underline from "@tiptap/extension-underline"
import Strike from "@tiptap/extension-strike"
import ListItem from "@tiptap/extension-list-item"
import BulletList from "@tiptap/extension-bullet-list"
import OrderedList from "@tiptap/extension-ordered-list"
import Link from "@tiptap/extension-link"
import TextAlign from '@tiptap/extension-text-align'
import { Blockquote } from "@tiptap/extension-blockquote"
import { HardBreak } from "@tiptap/extension-hard-break"
import { CharacterCount } from "@tiptap/extension-character-count"
import { Youtube } from "@tiptap/extension-youtube"
import Dropcursor from "@tiptap/extension-dropcursor"
import { HorizontalRule } from "@tiptap/extension-horizontal-rule"
import { Table } from "@tiptap/extension-table"
import { TableHeader } from "@tiptap/extension-table-header"
import { TableRow } from "@tiptap/extension-table-row"
import { TableCell } from "@tiptap/extension-table-cell"
import Gapcursor from "@tiptap/extension-gapcursor"
import Image from "@tiptap/extension-image"
import TextStyle from '@tiptap/extension-text-style'
import customLink from '@/Components/Forms/Fields/BubleTextEditor/CustomLink/CustomLinkExtension.js'
import { Color } from '@tiptap/extension-color'
/* import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue' */
import ColorPicker from 'primevue/colorpicker';

import {
    faUndo,
    faRedo,
    faQuoteLeft,
    faBold,
    faH1,
    faH2,
    faH3,
    faItalic,
    faLink,
    faUnderline,
    faStrikethrough,
    faImage,
    faVideo,
    faMinus,
    faList,
    faListOl,
    faAlignLeft,
    faAlignCenter,
    faAlignRight,
    faFileVideo,
    faPaintBrushAlt,
    faText
} from "@far"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

import TiptapLinkCustomDialog from "@/Components/Forms/Fields/BubleTextEditor/TiptapCustomLinkDialog.vue"
import TiptapLinkDialog from "@/Components/Forms/Fields/BubleTextEditor/TiptapLinkDialog.vue"
import TiptapVideoDialog from "@/Components/Forms/Fields/BubleTextEditor/TiptapVideoDialog.vue"
/* import TiptapTableDialog from "@/Components/Forms/Fields/BubleTextEditor/TiptapTableDialog.vue" */
import TiptapImageDialog from "@/Components/Forms/Fields/BubleTextEditor/TiptapImageDialog.vue"

const props = withDefaults(defineProps<{
    modelValue: string,
    toogle?: string[],
    type?: string,
    editable?: boolean
    placeholder?: any | String
}>(), {
    editable: true,
    type: 'Bubble',
    placeholder: '',
    toogle: () => [
        'heading', 'fontSize', 'bold', 'italic', 'underline', 'bulletList',
        'orderedList', 'blockquote', 'divider', 'alignLeft', 'alignRight', "customLink",
        'alignCenter', 'undo', 'redo', 'highlight', 'color', 'clear', "image", "video"
    ]
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: string): void
    (e: 'onEditClick', value: any): void
}>()

const _bubbleMenu = ref(null)
const showDialog = ref(false)
const contentResult = ref<string>()
const currentLinkInDialog = ref<string | undefined>()
const showLinkDialogCustom = ref<boolean>()
const showAddYoutubeDialog = ref<boolean>(false)
const showAddTableDialog = ref<boolean>(false)
const showAddImageDialog = ref<boolean>(false)
const showLinkDialog = ref<boolean>()

const editorInstance = useEditor({
    content: props.modelValue,
    editable: props.editable,
    /*  content: `
     <p>This is a paragraph.
 
      <CustomLinkExtension
       type="internal"
       workshop="https://tailwindcss.com/docs/z-index"
       id="1"
       url="https://tailwindcss.com/docs/z-index">link test
       </CustomLinkExtension>
 
       <CustomLinkExtension url="https://ancientwisdom.biz/showroom" 
       type="internal"
        id="9" 
        workshop="http://app.aiku.test/org/aw/shops/uk/web/aw/webpages/showroom-uk/workshop"   
        rel="noopener noreferrer" 
        target="_blank">
        <span style="{color: rgb(232, 121, 40)}">Showroom</span>
        </CustomLinkExtension>
       </p>
   `, */
    editorProps: {
        attributes: {
            class: "blog",
        },
    },
    extensions: [
        Paragraph,
        Document,
        Text,
        History,
        customLink,
        Heading.configure({
            levels: [1, 2, 3],
        }),
        Bold,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Italic,
        Underline,
        Strike,
        ListItem,
        BulletList,
        OrderedList,
        Link.configure({
            openOnClick: false,
        }),
        HardBreak,
        Blockquote,
        CharacterCount,
        Youtube,
        Dropcursor.configure({
            width: 2,
            color: "#2563eb",
        }),
        HorizontalRule,
        Table.configure({
            resizable: false,
            allowTableNodeSelection: true,
        }),
        TableRow,
        TableHeader,
        TableCell,
        Gapcursor,
        Image,
        TextStyle,
        Color.configure({
            types: ['textStyle'],
        }),
    ],
    onUpdate: ({ editor }) => {
        contentResult.value = editor.getHTML()
        emits('update:modelValue', editor.getHTML())
    },
})

function openLinkDialogCustom() {
    const attrs = editorInstance.value?.getText({ blockSeparator: '\n\n' })
    showLinkDialogCustom.value = true;
    showDialog.value = true;
}

function updateLinkCustom(value: string) {
    if (value.url) {
        const attrs = {
            type: value.type,
            workshop: value.type === 'internal' ? value.workshop : null,
            id: value.type === 'internal' ? value.id.id : null,
            url: value.url,
            content: value.content ? value.content : value.url,
        };

        editorInstance.value
            ?.chain()
            .focus()
            .insertContent({
                type: 'customLink',
                attrs,
            })
            .run();
    }
}

function openLinkDialog() {
    currentLinkInDialog.value = editorInstance.value?.getAttributes("link").href
    showLinkDialog.value = true
    showDialog.value = true;
}

function updateLink(value?: string) {
    if (!value) {
        editorInstance.value
            ?.chain()
            .focus()
            .extendMarkRange("link")
            .unsetLink()
            .run()
        return
    }

    editorInstance.value
        ?.chain()
        .focus()
        .extendMarkRange("link")
        .setLink({ href: value })
        .run()
}



function insertImage(url: string) {
    editorInstance.value?.chain().focus().setImage({ src: url }).run()
}

function insertYoutubeVideo(url: string) {
    editorInstance.value?.commands.setYoutubeVideo({
        src: url,
        width: 400,
        height: 300,
    })
}

function insertTable(table: DataTable) {
    editorInstance.value
        ?.chain()
        .focus()
        .insertTable({
            rows: table.rows,
            cols: table.columns,
            withHeaderRow: table.withHeader,
        })
        .run()
}

onMounted(() => {
    setTimeout(() => (contentResult.value = editorInstance.value?.getHTML()), 250)
})

onBeforeUnmount(() => {
    editorInstance.value?.destroy()
})
</script>

<template>
    <div id="tiptap" class="divide-y divide-gray-400">
        <BubbleMenu ref="_bubbleMenu" :editor="editorInstance" :tippy-options="{ duration: 100 }"
            v-if="editorInstance && !showDialog">
            <section id="tiptap-toolbar" class="bg-gray-100 rounded-xl border border-gray-300 divide-x divide-gray-400">
                <TiptapToolbarGroup>
                    <TiptapToolbarButton v-if="toogle.includes('undo')" label="Undo"
                        @click="editorInstance?.chain().focus().undo().run()"
                        :disabled="!editorInstance?.can().chain().focus().undo().run()">
                        <FontAwesomeIcon :icon="faRedo" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('redo')" label="Redo"
                        @click="editorInstance?.chain().focus().redo().run()"
                        :disabled="!editorInstance?.can().chain().focus().redo().run()">
                        <FontAwesomeIcon :icon="faUndo" class="h-5 w-5" />
                    </TiptapToolbarButton>
                </TiptapToolbarGroup>
                <TiptapToolbarGroup>
                    <TiptapToolbarButton v-if="toogle.includes('heading')" label="Heading 1"
                        :is-active="editorInstance?.isActive('heading', { level: 1 })"
                        @click="editorInstance?.chain().focus().toggleHeading({ level: 1 }).run()">
                        <FontAwesomeIcon :icon="faH1" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('heading')" label="Heading 2"
                        :is-active="editorInstance?.isActive('heading', { level: 2 })"
                        @click="editorInstance?.chain().focus().toggleHeading({ level: 2 }).run()">
                        <FontAwesomeIcon :icon="faH2" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('heading')" label="Heading 3"
                        :is-active="editorInstance?.isActive('heading', { level: 3 })"
                        @click="editorInstance?.chain().focus().toggleHeading({ level: 3 }).run()">
                        <FontAwesomeIcon :icon="faH3" class="h-5 w-5" />
                    </TiptapToolbarButton>
                </TiptapToolbarGroup>

                <TiptapToolbarGroup>
                    <TiptapToolbarButton v-if="toogle.includes('bold')" label="Bold"
                        :is-active="editorInstance?.isActive('bold')"
                        @click="editorInstance?.chain().focus().toggleBold().run()">
                        <FontAwesomeIcon :icon="faBold" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('italic')" label="Italic"
                        :is-active="editorInstance?.isActive('italic')"
                        @click="editorInstance?.chain().focus().toggleItalic().run()">
                        <FontAwesomeIcon :icon="faItalic" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('underline')" label="Underline"
                        :is-active="editorInstance?.isActive('underline')"
                        @click="editorInstance?.chain().focus().toggleUnderline().run()">
                        <FontAwesomeIcon :icon="faUnderline" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('strikethrough')" label="Strikethrough"
                        :is-active="editorInstance?.isActive('strike')"
                        @click="editorInstance?.chain().focus().toggleStrike().run()">
                        <FontAwesomeIcon :icon="faStrikethrough" class="h-5 w-5" />
                    </TiptapToolbarButton>

                    <TiptapToolbarButton v-if="toogle.includes('color')" label="Text Color">
                        <ColorPicker v-model="editorInstance.getAttributes('textStyle').color"
                            @update:model-value="color => editorInstance?.chain().focus().setColor(`#${color}`).run()" />
                    </TiptapToolbarButton>

                    <!-- <ColorPicker v-if="toogle.includes('highlight')" :color="editorInstance?.getAttributes('highlight').color"
                        @changeColor="(color) => editorInstance?.chain().setHighlight({ color: color.hex }).run()"
                        class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer p-1 border border-gray-400"
                        :style="{ backgroundColor: editorInstance?.getAttributes('highlight').color }">
                        <FontAwesomeIcon :icon="faPaintBrushAlt" class='text-gray-500 h-5 w-5' fixed-width
                            aria-hidden='true' />
                    </ColorPicker> -->

                </TiptapToolbarGroup>

                <TiptapToolbarGroup>
                    <TiptapToolbarButton v-if="toogle.includes('bulletList')" label="Bullet List"
                        :is-active="editorInstance?.isActive('bulletList')"
                        @click="editorInstance?.chain().focus().toggleBulletList().run()">
                        <FontAwesomeIcon :icon="faList" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('orderedList')" label="Ordered List"
                        :is-active="editorInstance?.isActive('orderedList')"
                        @click="editorInstance?.chain().focus().toggleOrderedList().run()">
                        <FontAwesomeIcon :icon="faListOl" class="h-5 w-5" />
                    </TiptapToolbarButton>
                </TiptapToolbarGroup>

                <TiptapToolbarGroup>
                    <TiptapToolbarButton v-if="toogle.includes('alignLeft')" label="Align Left"
                        :is-active="editorInstance?.isActive('textAlign', 'left')"
                        @click="editorInstance?.chain().focus().setTextAlign('left').run()">
                        <FontAwesomeIcon :icon="faAlignLeft" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('alignCenter')" label="Align Center"
                        :is-active="editorInstance?.isActive('textAlign', 'center')"
                        @click="editorInstance?.chain().focus().setTextAlign('center').run()">
                        <FontAwesomeIcon :icon="faAlignCenter" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('alignRight')" label="Align Right"
                        :is-active="editorInstance?.isActive('textAlign', 'right')"
                        @click="editorInstance?.chain().focus().setTextAlign('right').run()">
                        <FontAwesomeIcon :icon="faAlignRight" class="h-5 w-5" />
                    </TiptapToolbarButton>
                </TiptapToolbarGroup>

                <TiptapToolbarGroup>
                    <TiptapToolbarButton v-if="toogle.includes('link')" label="Link" @click="openLinkDialog"
                        :is-active="editorInstance?.isActive('link')">
                        <FontAwesomeIcon :icon="faLink" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('customLink')" label="Link Internal & External"
                        @click="openLinkDialogCustom" :is-active="editorInstance?.isActive('customLink')">
                        <FontAwesomeIcon :icon="faLink" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('image')" label="Image"
                        @click="showAddImageDialog = true">
                        <FontAwesomeIcon :icon="faImage" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('video')" label="Youtube Video"
                        @click="() => { showAddYoutubeDialog = true, showDialog = true }">
                        <FontAwesomeIcon :icon="faFileVideo" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('blockquote')" label="Blockquote"
                        :is-active="editorInstance?.isActive('blockquote')"
                        @click="editorInstance?.chain().focus().toggleBlockquote().run()">
                        <FontAwesomeIcon :icon="faQuoteLeft" class="h-5 w-5" />
                    </TiptapToolbarButton>
                    <TiptapToolbarButton v-if="toogle.includes('divider')"
                        @click="editorInstance?.chain().focus().setHorizontalRule().run()" label="Horizontal Line">
                        <FontAwesomeIcon :icon="faMinus" class="h-5 w-5" />
                    </TiptapToolbarButton>
                </TiptapToolbarGroup>
            </section>
        </BubbleMenu>

        <div class="flex flex-col">
            <EditorContent :editor="editorInstance" />
        </div>

        <TiptapLinkCustomDialog v-if="showLinkDialogCustom" :show="showLinkDialogCustom" :attribut="currentLinkInDialog"
            @close="() => { showLinkDialogCustom = false; showDialog = false; }" @update="updateLinkCustom" />
        <TiptapImageDialog v-if="showAddImageDialog" :show="showAddImageDialog" @close="showAddImageDialog = false"
            @insert="insertImage" />
        <TiptapLinkDialog v-if="showLinkDialog" :show="showLinkDialog" :current-url="currentLinkInDialog"
            @close="() => { showLinkDialog = false; showDialog = false; }" @update="updateLink" />
        <TiptapVideoDialog v-if="showAddYoutubeDialog" :show="showAddYoutubeDialog" @insert="insertYoutubeVideo"
            @close="() => { showAddYoutubeDialog = false; showDialog = false; }" />
    </div>
</template>


<style scoped>
:deep(.tippy-box) {
    min-width: 10px !important;
    max-width: max-content !important
}

:deep(.font-inter) {
    font-family: "Inter", sans-serif;
}

:deep(.ProseMirror) {
    @apply focus:outline-none px-0 py-0 min-h-[10px] relative;
}

:deep(.blog) {
    @apply flex flex-col space-y-4;
}

:deep(.blog h1) {
    @apply text-4xl font-semibold;
}

:deep(.blog h2) {
    @apply text-3xl font-semibold;
}

:deep(.blog h3) {
    @apply text-2xl font-semibold;
}

:deep(.blog ol),
:deep(.blog ul) {
    @apply ml-8 list-outside mt-2;
}

:deep(.blog ol) {
    @apply list-decimal;
}

:deep(.blog ul) {
    @apply list-disc;
}

:deep(.blog ol li),
:deep(.blog ul li) {
    @apply mt-2 first:mt-0;
}

:deep(.blog blockquote) {
    @apply italic border-l-4 border-gray-300 p-4 py-2 ml-6 mt-6 mb-2 bg-gray-50;
}

:deep(.blog a) {
    @apply hover:underline text-blue-600 cursor-pointer;
}

:deep(.blog hr) {
    @apply border-gray-400 my-4;
}

:deep(.blog table) {
    @apply border border-gray-400 table-fixed border-collapse w-full my-4;
}

:deep(.blog table th),
:deep(.blog table td) {
    @apply border border-gray-400 py-2 px-4 text-left relative;
}

:deep(.blog table th) {
    @apply bg-blue-100 font-semibold;
}

:deep(.blog .tableWrapper) {
    @apply overflow-auto;
}

:deep(.ProseMirror iframe) {
    @apply w-full h-auto max-w-[480px] min-h-[320px] aspect-video mr-6;
}

:deep(.ProseMirror img) {
    @apply mr-6 w-full max-w-[480px] max-h-[320px] object-contain object-center;
}

:deep(.ProseMirror img.ProseMirror-selectednode),
:deep(.ProseMirror div[data-youtube-video]) {
    @apply cursor-move;
}

:deep(.ProseMirror .selectedCell:after) {
    @apply z-[2] absolute inset-0 bg-gray-400/30 pointer-events-none content-[''];
}

:deep(.ProseMirror-gapcursor) {
    @apply hidden pointer-events-none relative;
    @apply after:content-[''] after:block after:relative after:h-5 after:border-l after:border-t-0 after:border-black after:mt-1;
}

:deep(.ProseMirror-gapcursor:after) {
    animation: ProseMirror-cursor-blink 1.1s steps(2, start) infinite;
}

@keyframes ProseMirror-cursor-blink {
    to {
        visibility: hidden;
    }
}

:deep(.ProseMirror-focused .ProseMirror-gapcursor) {
    @apply block;
}
</style>
