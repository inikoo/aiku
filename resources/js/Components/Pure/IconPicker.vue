<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Popover from 'primevue/popover';
import { library, icon } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faGalaxy, faTimesCircle } from '@fas';
import { faBaby, faCactus, faCircle, faObjectGroup, faUser } from '@fal';
import { faLambda } from '@fad';
import { faBackpack } from '@far';

// Add icons to the library
library.add(faTimesCircle, faUser, faCactus, faBaby, faObjectGroup, faGalaxy, faLambda, faBackpack);

const props = withDefaults(
    defineProps<{
        modelValue: string | SVGElement;
        iconList: Array<string>;
        listType: string;
        valueType : string
    }>(),
    {
        iconList : [],
        valueType : 'fontawesome',
        listType: 'extend',
    }
);

const _popover = ref();
const allIcons = props.listType == 'extend' ? [faTimesCircle, faUser, faCactus, faBaby, faObjectGroup, faGalaxy, faLambda, faBackpack , ...props.iconList ] : props.listType

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | SVGElement): void;
}>();

const toggle = (event: Event) => {
    _popover.value?.toggle(event);
};

const renderIcon = (iconData: any) => {
    if (iconData) {
        // If iconData is an SVG string, return it as HTML
        if (typeof iconData === 'string' && iconData.startsWith('<svg')) {
            return iconData;
        }

        // Otherwise, assume it's a FontAwesome icon and get its SVG HTML
        return icon(iconData).html[0];
    }

    return icon(faCircle).html[0];
   
};

const onChangeIcon = (iconData) => {
    if(props.valueType == 'fontawesome'){
        emits('update:modelValue', iconData)
    }else {
        emits('update:modelValue', icon(iconData).html[0])
    }
 
}

defineExpose({
    allIcons,
    popover: _popover,
});
</script>

<template>
    <!-- Trigger to toggle popover -->
    <span v-html="renderIcon(modelValue)" @click="toggle"></span>

    <!-- Popover content -->
    <Popover ref="_popover">
        <div class="grid grid-cols-4 gap-4 w-full max-w-[25rem]">
            <!-- Loop through the allIcons array -->
            <div 
                v-for="(iconData, index) in allIcons" 
                :key="index"
                class="flex flex-col items-center justify-center border border-gray-300 p-2 rounded-lg hover:bg-gray-100 cursor-pointer transition duration-300"
                @click="()=>onChangeIcon(iconData)"
            >
                <!-- Render the icon -->
                <span v-html="renderIcon(iconData)" class="text-gray-700 text-lg"></span>
                <!-- Render the label -->
                <span class="mt-2 text-xs text-gray-600 text-center">{{ iconData.iconName || 'SVG' }}</span>
            </div>
        </div>
    </Popover>
</template>

<style scoped>
/* Add custom styles if necessary */
</style>
