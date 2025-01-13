<script setup lang="ts">
import { faCube, faStar, faImage } from "@fas";
import { faPencil } from "@far";
import { library } from "@fortawesome/fontawesome-svg-core";
import EditorV2 from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue";

// Add FontAwesome icons to the library
library.add(faCube, faStar, faImage, faPencil);

const props = defineProps<{
    modelValue: any;
    webpageData?: any;
    blockData: Record<string, any>;
}>();

const emits = defineEmits<{
    (e: "update:modelValue", value: any): void;
    (e: "autoSave"): void;
}>();

/**
 * Get the CSS class for column width based on layout type and index
 */
const getColumnWidthClass = (layoutType: string, index: number): string => {
    const layoutClasses: Record<string, string[]> = {
        "12": ["sm:w-1/2 md:w-1/3", "sm:w-1/2 md:w-2/3"],
        "21": ["sm:w-1/2 md:w-2/3", "sm:w-1/2 md:w-1/3"],
        "13": ["md:w-1/4", "md:w-3/4"],
        "31": ["sm:w-1/2 md:w-3/4", "sm:w-1/2 md:w-1/4"],
        "211": ["md:w-1/2", "md:w-1/4"],
        "2": ["md:w-1/2", "md:w-1/2"],
        "3": ["md:w-1/3", "md:w-1/3"],
        "4": ["md:w-1/4", "md:w-1/4"],
    };
    return layoutClasses[layoutType]?.[index] || "w-full";
};

/**
 * Get the number of image slots based on layout type
 */
const getImageSlots = (layoutType: string): number => {
    const slotCounts: Record<string, number> = {
        "4": 4,
        "3": 3,
        "211": 3,
        "2": 2,
        "12": 2,
        "21": 2,
        "13": 2,
        "31": 2,
    };
    return slotCounts[layoutType] || 1;
};

</script>

<template>
    <div class="flex flex-wrap">
        <div
            v-for="(slot, index) in getImageSlots(modelValue?.value?.layout_type)"
            :key="`${index}`"
            class="group relative p-2"
            :class="getColumnWidthClass(modelValue?.value?.layout_type, index)"
        >
            <EditorV2
                v-model="modelValue.value.text[index]"
                @update:modelValue="() => emits('autoSave')"
            />
        </div>
    </div>
</template>
