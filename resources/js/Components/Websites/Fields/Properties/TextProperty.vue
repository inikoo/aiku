<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faLink, faUnlink } from "@fal"
import { faExclamation } from "@fas"
import ColorPicker from '@/Components/Utils/ColorPicker.vue'
library.add(faExclamation, faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter, faLink, faUnlink)

interface Borderproperty {
    color: string,
    fontFamily : String
}

const model = defineModel<Borderproperty>({
    required: true
})

const fontFamilies = ([
  "Arial, sans-serif",
  "Verdana, sans-serif",
  "Times New Roman, serif",
  "Georgia, serif",
  "Courier New, monospace",
  "Lucida Console, monospace"
]);

</script>

<template>
    <div class="flex flex-col pt-1 pb-3">

        <div class="pb-2">
            <div class="px-3 flex justify-between items-center mb-2">
                <div class="text-xs">{{ trans('Color') }}</div>
                <ColorPicker :color="model.color"
                    @changeColor="(newColor) => model.color = `rgba(${newColor.rgba.r}, ${newColor.rgba.g}, ${newColor.rgba.b}, ${newColor.rgba.a}`"
                    closeButton>
                    <template #button>
                        <div v-bind="$attrs"
                            class="overflow-hidden h-7 w-7 rounded-md border border-gray-300 cursor-pointer flex justify-center items-center"
                            :style="{
                                background: `${model.color}`
                            }">
                        </div>
                    </template>
                </ColorPicker>
            </div>

            <div class="px-3 items-center">
                <div class="text-xs mb-2">{{ trans('Font Families') }}</div>
                <div class="col-span-4">
                    <PureMultiselect 
                    v-model="model.fontFamily" 
                    class="" 
                    :options="fontFamilies"
                    />
                </div>
            </div>

        </div>
    </div>
</template>