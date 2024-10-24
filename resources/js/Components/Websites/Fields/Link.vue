<script setup lang="ts">
import { ref, watch, defineEmits } from 'vue'
import { trans } from 'laravel-vue-i18n'
import SelectButton from 'primevue/selectbutton'
import PureInput from '@/Components/Pure/PureInput.vue';
import SelectQuery from "@/Components/SelectQuery.vue";

// Define props
const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    }
});

const emit = defineEmits(['update:modelValue']);
const options = ref([
    { label: 'Internal', value: 'internal' },
    { label: 'External', value: 'external' },
]);


</script>

<template>
    <div class="pb-3">
        <SelectButton v-model="modelValue.type" :options="options" optionLabel="label" optionValue="value">
            <template #option="slotProps">
                <span class="text-xs">{{ slotProps.option.label }}</span>
            </template>
        </SelectButton>
    </div>
    <div>
        <div class="my-2 text-gray-500 text-xs font-semibold mb-2">{{ trans('Link') }}</div>
        <PureInput v-if ="modelValue.type == 'external'" v-model="modelValue.url" />
        <SelectQuery 
            v-else
            fieldName="id" 
            :object="true" 
            :urlRoute="route('grp.org.shops.show.web.webpages.index', {
                organisation: route().params['organisation'],
                shop: route().params['shop'],
                website: route().params['website']
            })" 
            :value="modelValue" 
            :closeOnSelect="true" 
            label="url"
        />
    </div>

</template>

<style lang="scss" scoped></style>
