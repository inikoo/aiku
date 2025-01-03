<script setup lang="ts">
import Button from '@/Components/Elements/Buttons/Button.vue';
import { notify } from '@kyvg/vue3-notification';
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faSyncAlt, faHistory } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add( faSyncAlt);



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

const historyPin = () =>{
    const data = { ...props.form, [props.fieldName] :  originalPin}
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
        <Button @click="historyPin" type="tertiary" :icon="faHistory" size="xs"  />
        <Button @click="generateNewPin" label="generate" :icon="faSyncAlt" size="xs" :loading="loading" />
    </div>
   
    <div class="flex items-center space-x-2">
        <div v-for="(value, index) in form[fieldName]" :key="index" class="relative">
            <input type="text" disabled maxlength="1" v-model="form[fieldName][index]"
                class="w-10 h-10 text-center text-xs font-semibold border-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
    </div>
</template>
