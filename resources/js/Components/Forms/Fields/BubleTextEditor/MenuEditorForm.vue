<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Select from 'primevue/select';
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faText, faUndoAlt, faRedoAlt } from '@far'
import { faHorizontalRule, faQuoteRight, faMarker } from '@fas'
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faPaintBrushAlt, faTextHeight, faLink } from '@fal'

// Adding FontAwesome icons to the library
library.add(faBold, faQuoteRight, faMarker, faHorizontalRule, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, faPaintBrushAlt, faTextHeight, faLink, faText)

// Props definition
const props = withDefaults(defineProps<{
    editor: any,
    toggleList: Array<any>,
}>(), {
});

// Reactive references for text style and alignment
const textStyle = ref(props.toggleList.filter((item) => ['bold', 'italic', 'underline'].includes(item.key)));

const align = ref(props.toggleList.filter((item) => ['alignLeft', 'alignCenter', 'alignRight'].includes(item.key)));

// Function to find action in toggleList based on key
const findAction = (key: string) => {
    return props.toggleList.some(item => item.key === key);
}

const fontfamily = ref();
const fontfamilies = ref([
     'Inter','Comic Sans MS, Comic Sans', 'serif' , 'IST' ,'monospace','cursive'
]);

</script>

<template>
    <!-- Text Style Section -->
    <div v-if="textStyle.length > 0" class="mb-4 flex justify-between items-center bg-white">
        <div class="text-sm font-medium text-gray-700">Text Style</div>
        <div class="flex items-center">
            <button v-for="action in textStyle" :key="action.key" type="button" @click="action.action" :class="{
                'bg-gray-300 text-gray-800': editor?.isActive(action.active),
                'bg-gray-100 text-gray-600 hover:bg-gray-200': !editor?.isActive(action.active)
            }" class="px-1 py-0.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                <FontAwesomeIcon v-if="action.icon" :icon='action.icon' class="h-4 w-4" />
            </button>
        </div>
    </div>

    <!-- Align Style Section -->
    <div v-if="align.length > 0" class="mb-4 flex justify-between items-center bg-white">
        <div class="text-sm font-medium text-gray-700">Align Style</div>
        <div class="flex items-center">
            <button v-for="action in align" :key="action.key" type="button" @click="action.action" :class="{
                'bg-gray-300 text-gray-800': editor?.isActive(action.active),
                'bg-gray-100 text-gray-600 hover:bg-gray-200': !editor?.isActive(action.active)
            }" class="px-1 py-0.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                <FontAwesomeIcon v-if="action.icon" :icon='action.icon' class="h-4 w-4" />
            </button>
        </div>
    </div>

    <!-- Color Picker Section -->
    <div v-if="editor && findAction('color')" class="mb-4 flex justify-between items-center bg-white">
        <div class="text-sm font-medium text-gray-700">Color</div>
        <div class="flex items-center">
            <input type="color" @input="editor.chain().focus().setColor($event.target.value).run()"
                :value="editor.getAttributes('textStyle').color">
        </div>
    </div>

    <!-- Font Size Section -->
    <div v-if="editor && findAction('fontSize')" class="mb-4 flex justify-between items-center bg-white">
        <div class="text-sm font-medium text-gray-700">Font Size</div>
        <div class="flex items-center">
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
                        :class="{ 'bg-slate-700 text-white hover:bg-slate-700': parseInt(editor?.getAttributes('textStyle').fontSize, 10) == fontsize }"
                        @click="editor?.chain().focus().setFontSize(fontsize + 'px').run()" role="button">
                        {{ fontsize }}
                    </div>
                </div>
            </div>
        </div>
    </div>


     <div v-if="editor && findAction('color')" class="mb-4 flex justify-between items-center bg-white">
        <div class="text-sm font-medium text-gray-700">Font Family</div>
        <div class="flex items-center">
            <Select v-model="fontfamily" :options="fontfamilies" @update:modelValue="editor.chain().focus().setFontFamily('Comic Sans MS, Comic Sans').run()" class="p-0"/>
        </div>
    </div>

</template>

<style lang="scss" scoped>
.p-select-label {
    padding : 0px
}
</style>
