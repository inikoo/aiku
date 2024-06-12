<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PureInput from "@/Components/Pure/PureInput.vue"
import { ref } from "vue"
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import Popover from "@/Components/Popover.vue"

import ColorPicker from '@/Components/CMS/Fields/ColorPicker.vue'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faText, faUndoAlt, faRedoAlt } from '@far'
import { faHorizontalRule, faQuoteRight, faMarker } from '@fas'
import { faBold, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faPaintBrushAlt, faTextHeight, faLink } from '@fal'
library.add(faBold, faQuoteRight, faMarker, faHorizontalRule, faItalic, faUnderline, faStrikethrough, faAlignLeft, faAlignCenter, faAlignRight, faAlignJustify, faSubscript, faSuperscript, faEraser, faListUl, faListOl, faUndoAlt, faRedoAlt, faPaintBrushAlt, faTextHeight, faLink, faText)


const props = defineProps<{
    modelValue: any
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()


const fontSizes = [
    {
        label: 'xs',
        value : {
             fontSize: '0.75rem',
             lineHeight: '1rem',
        }
    },
    {
        label: 'sm',
        value : {
             fontSize: '0.875rem',
             lineHeight: '1.25rem',
        }
    },
    {
        label: 'base',
        value : {
             fontSize: '1rem',
             lineHeight: '1.5rem',
        }
    },
    {
        label: 'lg',
        value : {
             fontSize: '1.125rem',
             lineHeight: '1.75rem',
        }
    },
    {
        label: 'xl',
        value : {
             fontSize: '1.25rem',
             lineHeight: '1.75rem',
        }
    },
    {
        label: '2xl',
        value : {
             fontSize: '1.5rem',
             lineHeight: '2rem',
        }
    },
    {
        label: '3xl',
        value : {
             fontSize: '1.875rem',
             lineHeight: '2.25rem',
        }
    },
    {
        label: '4xl',
        value : {
             fontSize: '2.25rem',
             lineHeight: '2.5rem',
        }
    },
]

const onActionClick = (key: string, option: string = '') => {
    switch (key) {
        case 'bold':
            emits('update:modelValue', {...props.modelValue, fontWeight : props.modelValue?.fontWeight == 700 ? null : 700 })
            break;
        case 'alignLeft':
        emits('update:modelValue', {...props.modelValue, textAlign: 'left' })
            break;
        case 'alignRight':
            emits('update:modelValue', {...props.modelValue, textAlign: 'right' })
            break;
        case 'alignCenter':
            emits('update:modelValue', {...props.modelValue, textAlign: 'center' })
            break;
        case 'clear':
            emits('update:modelValue', {})
            break;
        default:
            console.warn(`Action not found for key: ${key}`);
            break;
    }
}

const toggleList = ref([
    { key: 'fontSize' },
    { key: 'bold', icon: 'fal fa-bold', action: () => onActionClick('bold') },
    { key: 'highlight', icon: 'fal fa-paint-brush-alt', action: () => onActionClick('highlight') },
    { key: 'color', icon: 'far fa-text', action: () => onActionClick('color') },
    { key: 'clear', icon: 'fal fa-eraser', action: () => onActionClick('clear') },
    { key: 'alignLeft', icon: 'fal fa-align-left', action: () => onActionClick('alignLeft') },
    { key: 'alignRight', icon: 'fal fa-align-right', action: () => onActionClick('alignRight')},
    { key: 'alignCenter', icon: 'fal fa-align-center', action: () => onActionClick('alignCenter') },
]);


</script>

<template>
    <Popover class="relative h-full">
        <template #button>
            <slot name="content-area">
                xxxxx
            </slot>
        </template>

        <template #content="{ close: closed }">
            <div class="w-[200px]">
                <div class="buttons text-gray-700 flex items-center flex-wrap gap-x-2">
                    <section v-for="action in toggleList" :key="action.key">

                        <div v-if="action.key == 'fontSize'" class="group relative inline-block ">
                            <div class="flex items-center text-xs min-w-10 py-1 pl-1.5 pr-0 appearance-none rounded cursor-pointer border border-gray-500 leading-3"
                                :class="'bg-slate-700 text-white font-bold'">
                                <div id="tiptapfontsize" class="pr-1.5">
                                    <span class="hidden last:inline">Text size</span>
                                </div>
                            </div>
                            <div
                                class="w-min cursor-pointer overflow-hidden hidden group-hover:block absolute left-0 right-0 border border-gray-500 rounded bg-white z-[1]">
                                <div v-for="fontsize in fontSizes" @click="emits('update:modelValue', {...props.modelValue, ...fontsize.value })"
                                    class="w-full block py-1.5 px-3 leading-none text-left cursor-pointer hover:bg-gray-300 text-xs"
                                    role="button">
                                    {{ fontsize.label }}
                                </div>
                            </div>
                        </div>


                        <ColorPicker v-else-if="action.key == 'highlight'"
                            :color="modelValue?.backgroundColor"
                            @changeColor="(color) => emits('update:modelValue', {...props.modelValue, backgroundColor : color.hex })"
                            class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700 text-lg"
                            :style="{ backgroundColor: modelValue?.backgroundColor }">
                            <FontAwesomeIcon icon='fal fa-paint-brush-alt' class='text-gray-500 fixed-width' 
                                aria-hidden='true' />
                        </ColorPicker>


                        <ColorPicker v-else-if="action.key == 'color'" @changeColor="(color) => emits('update:modelValue', {...props.modelValue, color : color.hex })"
                            :color="modelValue?.color"
                            class="flex items-center justify-center w-6 aspect-square rounded cursor-pointer border border-gray-700 text-lg">
                            <FontAwesomeIcon icon='far fa-text' class="fixed-width"  aria-hidden='true'
                                :style="{ color: modelValue.color || '#010101' }" />
                        </ColorPicker>



                        <button v-else type="button" @click="action?.action" class="p-1 text-lg">
                            <span v-if="action.icon">
                                <FontAwesomeIcon :icon='action.icon' />
                            </span>
                        </button>

                    </section>
                </div>
            </div>
        </template>
    </Popover>
</template>