<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { cloneDeep } from 'lodash'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faShieldAlt } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons";

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt)

const props = defineProps<{
    modelValue: any,

}>();

const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>()



const payments = [
    { label: 'Checkout.com', key: 'checkout.com' },
    { label: 'Visa', key: 'visa' },
    { label: 'Paypal', key: 'paypal' },
    { label: 'Mastercard', key: 'mastercard' },
    { label: 'PastPay', key: 'PastPay' },
]

const addPayments = () => {
    let data = cloneDeep(props.modelValue.data)
    data.push(
        {
            name: "visa",
            key: "visa",
            image: "https://e7.pngegg.com/pngimages/687/457/png-clipart-visa-credit-card-logo-payment-mastercard-usa-visa-blue-company.png",
        },
    )
    emits('update:modelValue', { data : data })
}

</script>

<template>
    <div>
        <div v-for="item of modelValue.data" class="p-1">
            <PureMultiselect  v-model="item.key" :options="payments" label="label" valueProp="key" :required="true" />
        </div>
        <Button type="dashed" icon="fal fa-plus" label="Add Payments Method" full size="s" class="mt-2"
            @click="addPayments" />
    </div>
</template>


<style scss></style>
