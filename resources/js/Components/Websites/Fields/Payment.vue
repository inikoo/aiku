<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { cloneDeep } from 'lodash'
import { router } from '@inertiajs/vue3'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faShieldAlt, faTimes, faTrash } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons";

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faTimes, faTrash)

const props = defineProps<{
    modelValue: any,
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>();

const GetPayment = async () => {
    try {
        const response = await axios.get(
            route('grp.org.accounting.org-payment-service-providers.index', { organisation: route().params['organisation'] }),
        )

        if (response && response.data && response.data.data) {
            const ini = response.data.data.map((item) => ({
                name: item.name,
                value: item.name,
                image: item.logo
            }))
            payments.value = ini
        } else {
            console.error('Invalid response format', response)
        }
    } catch (error: any) {
        console.error('error', error)
    }
}


const payments = ref([])

const addPayments = () => {
    let data = cloneDeep(props.modelValue.data);
    data.push(
        {
            label: "visa",
            value: "visa",
            image: "https://e7.pngegg.com/pngimages/687/457/png-clipart-visa-credit-card-logo-payment-mastercard-usa-visa-blue-company.png",
        },
    );
    emits('update:modelValue', { data: data });
};

const updatePayment = (index: number, value: any) => {
    let data = cloneDeep(props.modelValue.data);
    data[index] = {
        name: value.name,
        value: value.name,
        image: value.image
    };
    emits('update:modelValue', { data });
};

const deleteSocial = (event, index) => {
    event.stopPropagation();
    event.preventDefault();
    let set = cloneDeep(props.modelValue.data);
    set.splice(index, 1)
    emits('update:modelValue', { data: set });
}

onMounted(() => {
    GetPayment()
});

</script>

<template>
    <div>
        <div v-for="(item, index) of modelValue.data" :key="index" class="p-1">
            <div class="flex">
                <PureMultiselect :modelValue="item" :options="payments" :object="true" :required="true"
                    @update:modelValue="value => updatePayment(index, value)">
                    <template v-slot:label="{ value }">
                        <div
                            class="flex items-center border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow w-full bg-gray-200 p-2 m-2">
                            <img class="w-12 h-12 rounded-full object-contain object-center group-hover:opacity-75"
                                :src="value.image" alt="avatar">
                            <div class="ml-4">
                                {{ value.name }}
                            </div>
                        </div>

                    </template>

                    <template v-slot:option="{ option }">
                        <div
                            class="flex items-center border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow w-full bg-gray-200 p-2 m-2">
                            <img class="w-12 h-12 rounded-full object-contain object-center group-hover:opacity-75"
                                :src="option.image" alt="avatar">
                            <div class="ml-4">
                                {{ option.name }}
                            </div>
                        </div>


                    </template>
                </PureMultiselect>
                <FontAwesomeIcon :icon="['fas', 'trash']" class="text-red-500 my-auto px-2 cursor-pointer"
                    @click="(e) => deleteSocial(e, index)" />
            </div>

        </div>
        <Button type="dashed" icon="fal fa-plus" label="Add Payments Method" full size="s" class="mt-2"
            @click="addPayments" />
    </div>
</template>

<style scoped></style>
