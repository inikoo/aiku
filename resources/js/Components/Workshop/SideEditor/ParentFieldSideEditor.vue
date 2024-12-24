<script setup lang="ts">
import { ref } from 'vue'

import AccordionPanel from 'primevue/accordionpanel'
import AccordionHeader from 'primevue/accordionheader'
import AccordionContent from 'primevue/accordioncontent'
import ChildFieldSideEditor from '@/Components/Workshop/SideEditor/ChildFieldSideEditor.vue'
import { trans } from 'laravel-vue-i18n'

import { getFormValue ,setFormValue, getComponent } from '@/Composables/SideEditorHelper'
import { get } from 'lodash'
import { routeType } from '@/types/route'
import Icon from '@/Components/Icon.vue'

const props = defineProps<{
    blueprint: {
        name : String,
        type : String,
        key : Array<String> | String,
        replaceForm : Array<any>
    }
    uploadImageRoute?: routeType,
    index:Number | Array<String> | string
}>()
const modelValue = defineModel()

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

</script>

<template>
    <AccordionPanel v-if="blueprint.name" :key="index" :value="index">
        <AccordionHeader>
            <div>
                <Icon v-if="blueprint?.icon" :data="blueprint.icon" />
                {{ get(blueprint, 'name', 'test') }}
            </div>
        </AccordionHeader>
        <AccordionContent class="px-0">
            <div class="">
                <template v-if="blueprint.replaceForm">
                    <ChildFieldSideEditor 
                        :blueprint="blueprint.replaceForm"
                        :modelValue="getFormValue(modelValue, blueprint.key)"
                        :key="blueprint.key"
                        :uploadImageRoute="uploadImageRoute" 
                        @update:modelValue="newValue => emits('update:modelValue',setFormValue(modelValue, blueprint.key, newValue))"
                    />
                </template>

                <template v-else >
                   <!--  <div class="my-2 text-xs font-semibold">{{ get(blueprint, 'label', '') }}</div> -->
                    <div 
                         class="w-full my-2 text-center py-1 font-semibold select-none text-sm "
                         :class="blueprint.label && 'border-b border-gray-300 py-2'"
                    >
                         {{ trans(get(blueprint, 'label', '')) }}
                    </div>
                    
                    <component 
                        :is="getComponent(blueprint.type)" 
                        :key="blueprint.key"
                        :modelValue="getFormValue(modelValue, blueprint.key)"
                        :uploadRoutes="uploadImageRoute" 
                        v-bind="blueprint?.props_data" 
                        @update:modelValue="newValue => emits('update:modelValue', setFormValue(modelValue, blueprint.key, newValue))"
                    />
                </template>
            </div>
        </AccordionContent>
    </AccordionPanel>

    <div v-else class="bg-white mt-[0px] mb-2  pb-3">
        <template v-if="blueprint.replaceForm">
            <ChildFieldSideEditor
                :blueprint="blueprint.replaceForm"
                :modelValue="getFormValue(modelValue, blueprint.key)"
                :key="blueprint.key"
                @update:modelValue="newValue => emits('update:modelValue',setFormValue(modelValue, blueprint.key, newValue))"
            />
        </template>

        <template v-else>
           <!-- Section: Padding, Margin, Border -->
            <div v-if="get(blueprint, 'label', '')" class="w-full my-2 text-start py-1 font-semibold select-none text-sm border-b border-gray-300">{{ trans(get(blueprint, 'label', '')) }}</div>
            <component 
                :is="getComponent(blueprint.type)" 
                :key="blueprint.key"
                :uploadRoutes="uploadImageRoute" 
                v-bind="blueprint?.props_data" 
                :modelValue="getFormValue(modelValue, blueprint.key)"
                @update:modelValue="newValue => {
                    // console.log(index, 'getfomvalue', getFormValue(modelValue, blueprint.key))
                    emits('update:modelValue', setFormValue(modelValue, blueprint.key, newValue))
                }"
            />
        </template>
    </div>

</template>


<style lang="scss" scoped>
.editor-content {
    background-color: white;
    border: solid;
}

.p-inputtext {
    width: 100%;
}

.p-accordionpanel.p-accordionpanel-active > .p-accordionheader {
    background-color: #433CC3 !important;
    border-radius: 0 !important;
    color: #fdfdfd !important;
}

.p-accordioncontent-content {
    padding: 10px !important;
    background-color: #F9F9F9 !important;
}

</style>
