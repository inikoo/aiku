<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Popover from 'primevue/popover';
import { library, icon } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faGalaxy, faTimesCircle } from '@fas';
import { faBaby, faCactus, faCircle, faObjectGroup, faUser, faHouse, faTruck, faTag, faPhone } from '@fal';
import { faLambda } from '@fad';
import { faBackpack } from '@far';

// Add icons to the library
library.add(faTimesCircle, faUser, faCactus, faBaby, faObjectGroup, faGalaxy, faLambda, faBackpack, faHouse, faTruck, faTag, faPhone );

const props = withDefaults(
    defineProps<{
        modelValue: string | SVGElement;
        iconList?: Array<string | [string, string]>;
        listType?: string;
        valueType?: string; // "fontawesome | string | svg | array"
    }>(),
    {
        iconList: [],
        valueType: 'fontawesome',
        listType: 'extend',
    }
);

const _popover = ref();
const allIcons = props.listType === 'extend' 
    ? [...[faTimesCircle, faUser, faCactus, faBaby, faObjectGroup, faGalaxy, faLambda, faBackpack, faHouse, faTruck, faTag, faPhone ], ...props.iconList] 
    : props.iconList;

const emits = defineEmits<{
    (e: 'update:modelValue', value: string | SVGElement): void;
}>();

const toggle = (event: Event) => {
    _popover.value?.toggle(event);
};

const renderIcon = (iconData: any) => {
 /*    return icon(faCircle).html[0]; */
    if (!iconData) return icon(faCircle).html[0];

    if (typeof iconData === 'string') {
        if (iconData.startsWith('<svg')) {
            return iconData; // SVG string
        } else {
            const [prefix, iconName] = iconData.split(' ');
            return icon({ prefix, iconName }).html[0]; // FontAwesome string
        }
    } else if (Array.isArray(iconData)) {
        const [prefix, iconName] = iconData;
 
        return icon({ prefix, iconName }).html[0];
    } else {
        return icon(iconData).html[0]; // Assume it's a FontAwesome object
    }
};

const onChangeIcon = (iconData: any) => {
    let updatedValue;

    if (props.valueType === 'fontawesome') {
        updatedValue = iconData;
    } else if (props.valueType === 'string') {
        if (typeof iconData === 'string') {
            updatedValue = iconData;
        } else if (Array.isArray(iconData)) {
            updatedValue = iconData.join(' ');
        } else {
            updatedValue = `${iconData.prefix} ${iconData.iconName}`;
        }
    } else if (props.valueType === 'svg') {
        updatedValue = icon(iconData).html[0];
    }else if (props.valueType === 'array'){
        updatedValue = [iconData.prefix, iconData.iconName]
    }

    emits('update:modelValue', updatedValue);
    _popover.value?.hide();
};

defineExpose({
    allIcons,
    popover: _popover,
    modelValue: props.modelValue,
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
                class="flex flex-col items-center justify-center p-2 rounded-lg hover:bg-gray-100 cursor-pointer transition duration-300"
                @click="() => onChangeIcon(iconData)"
            >
                <!-- Render the icon -->
                <span v-html="renderIcon(iconData)" class="text-gray-700 text-lg"></span>
                <!-- Render the label -->
               <!--  <span class="mt-2 text-xs text-gray-600 text-center">
                    {{ Array.isArray(iconData) ? iconData[1] : iconData.iconName || 'SVG' }}
                </span> -->
            </div>
        </div>
    </Popover>
</template>

<style scoped>
/* Add custom styles if necessary */
</style>
