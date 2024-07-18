<script setup lang="ts">
import { ref } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { cloneDeep } from 'lodash'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faShieldAlt, faTimes } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons";

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faTimes)

const props = defineProps<{
    modelValue: any,
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>();

const payments = [
    { label: 'Checkout.com', value: 'checkout.com', image: "https://www.linqto.com/wp-content/uploads/2023/04/logo_2021-11-05_19-04-11.530.png", },
    { label: 'Visa', value: 'visa', image: "https://e7.pngegg.com/pngimages/687/457/png-clipart-visa-credit-card-logo-payment-mastercard-usa-visa-blue-company.png", },
    { label: 'Paypal', value: 'paypal', image: "https://e7.pngegg.com/pngimages/292/77/png-clipart-paypal-logo-illustration-paypal-logo-icons-logos-emojis-tech-companies.png", },
    { label: 'Mastercard', value: 'mastercard', image: "https://i.pinimg.com/736x/38/2f/0a/382f0a8cbcec2f9d791702ef4b151443.jpg", },
    { label: 'PastPay', value: 'PastPay', image: "https://pastpay.com/wp-content/uploads/2023/07/PastPay-logo-dark-edge.png", },
];

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
    data[index] = { ...value };
    emits('update:modelValue', { data });
};

const deleteSocial = (event,index) => {
    event.stopPropagation();
    event.preventDefault();
    let set = cloneDeep(props.modelValue.data);
    set.splice(index,1)
    emits('update:modelValue',{ data: set });
}
</script>

<template>
    <div>
        <div v-for="(item, index) of modelValue.data" :key="index" class="p-1">
        <div class="flex">
            <PureMultiselect :modelValue="item" :options="payments" :object="true" :required="true"
                @update:modelValue="value => updatePayment(index, value)">
                <template v-slot:singlelabel="{ value }">
                    <div class="flex items-center border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow w-full bg-gray-200 p-2 m-2">
                        <img class="object-cover object-center group-hover:opacity-75 rounded-lg" :src="value.image">
                        <div class="ml-4">
                            {{ value.label }}
                        </div>
                    </div>
                </template>

                <template v-slot:option="{ option }">
                    <div class="flex items-center border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow w-full bg-gray-200 p-2 m-2">
                        <img class="object-cover object-center group-hover:opacity-75 rounded-lg" :src="option.image">
                        <div class="ml-4">
                            {{ option.name }}
                        </div>
                    </div>
                </template>
            </PureMultiselect>
            <FontAwesomeIcon :icon="['fas', 'times']" class="text-red-500 my-auto px-2 cursor-pointer" @click="(e)=>deleteSocial(e,index)"/>
        </div>
            
        </div>
        <Button type="dashed" icon="fal fa-plus" label="Add Payments Method" full size="s" class="mt-2" @click="addPayments" />
    </div>
</template>

<style scoped></style>
