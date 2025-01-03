<script setup lang="ts">
import Button from '@/Components/Elements/Buttons/Button.vue';
import { notify } from '@kyvg/vue3-notification';
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faSyncAlt, faHistory } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(faSyncAlt);



const props = defineProps<{
    form: any;
    fieldName: string;
    fieldData: {
        required: boolean;
    };
}>();

const emits = defineEmits()
const originalPin = props.form[props.fieldName]
const loading = ref(false)

const historyPin = () => {
    const data = { ...props.form, [props.fieldName]: originalPin }
    emits("update:form", data);
}

const generateNewPin = () => {
    router.get(route('generate-pin'), {}, {
        onFinish: () => {
            console.log('Request selesai');
        },
        onSuccess: (response: { pin: number[] }) => {
            props.form[props.fieldName] = response.pin;
        },
        onError: (error) => {
            notify({
                title: "Failed",
                text: "Error while fetching data",
                type: "error"
            })
        },
    });
};

</script>

<template>
    <div class="mb-4 flex space-x-2">
        <Button @click="historyPin" type="tertiary" :icon="faHistory" size="xs" />
        <Button @click="generateNewPin" label="generate" :icon="faSyncAlt" size="xs" :loading="loading" />
    </div>

    <div class="flex  gap-1 flex-wrap rounded-lg">
        <div v-for="(value, index) in Array.from(form[fieldName])" :key="index"
            class="relative w-10 h-10 flex items-center justify-center text-lg font-medium border border-gray-300 rounded-lg bg-white shadow-sm hover:bg-blue-50 transition duration-150">
            <span>{{ value }}</span>
        </div>
    </div>


</template>
