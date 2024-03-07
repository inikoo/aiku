<script setup lang="ts">
import { ref, Ref, onMounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import ValidationErrors from '@/Components/ValidationErrors.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import PurePassword from '@/Components/Pure/PurePassword.vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowLeft } from '@fal'
import { faSpinnerThird } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
import { notify } from '@kyvg/vue3-notification'
library.add(faArrowLeft, faSpinnerThird)

const props = defineProps<{
    emailField: {
        value: string
        status: string | boolean
        description: string
    }
    passwordField: {
        value: string
        valueRepeat: string
        description: string
    }
    loginRoute: string
    registerRoute: string
    checkEmailRoute: string
}>()

const emits = defineEmits<{
    (e: 'onSuccessLogin'): void
    (e: 'update:email', value: string): void
    (e: 'update:password', value: string): void
    (e: 'update:passwordRepeat', value: string): void
    (e: 'loginSuccess'): void
}>()

const isLoading = ref(false)

const form = useForm({
    email: 'aiku@inikoo.com',
    password: '',
    passwordRepeat: '',
    remember: false,
})

// On click check email registered or not
const onCheckEmail = async () => {
    try {
        const response = await axios.post(
            route(props.checkEmailRoute),
            {
                email: props.emailField.value
            }
        )
        props.emailField.status = 'success'
        props.emailField.description = 'Email is registered.'
        console.log(response.data)
    }
    catch (error: any) {
        props.emailField.status = 'error'
        props.emailField.description = 'Email is not registered yet.'
        console.log('error', error)
    }

    // form.post(route(props.checkEmailRoute), {
    //     onSuccess: () => {
    //         notify({
    //             title: "Email is exist.",
    //             // text: error,
    //             type: "success"
    //         })

    //         props.emailField.status = 'success'
    //         props.emailField.description = 'Email is registered.'
    //     },
    //     onError: (error: any) => {
    //         props.emailField.status = 'error'
    //         props.emailField.description = 'Email is not registered yet.'
    //     }
    // });
}

// On submit login
const submitFormLogin = async () => {
    form.post(route(props.loginRoute),
        {
            onFinish: () => { form.reset('password'), form.reset('passwordRepeat') },
            onSuccess: (response) => {
                notify({
                    title: "Login success.",
                    // text: error,
                    type: "success"
                })
                console.log("xxx", response)
                emits('loginSuccess')
            },
            onError: (error: any) => {
                notify({
                    // title: "Login success.",
                    text: error,
                    type: "error"
                })
            }
        })
}

// On submit register
const submitFormRegister = () => {
    form.post(route(props.registerRoute),
        {
            onFinish: () => { form.reset('password'), form.reset('passwordRepeat') },
            onSuccess: (response) => {
                notify({
                    title: "Register success.",
                    // text: error,
                    type: "success"
                })
                console.log("xxx", response)
                emits('loginSuccess')
            },
            onError: (error: any) => {
                notify({
                    // title: "Login success.",
                    text: error,
                    type: "error"
                })
            }
        })
}

// Check string a valid email or not
const validateEmail = (email: string) => {
    return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)
}

onMounted(() => {
    usePage().props.auth?.user ? emits('loginSuccess') : false
})

</script>

<template layout="Public">
    <div class="flex flex-col gap-y-6 w-96">
        <!-- {{ form }} -->

        <!-- Section: Enter email -->
        <div class="flex flex-col gap-x-2">
            <h3 class="text-sm font-semibold leading-3 text-gray-500">Enter your email</h3>
            <div class="flex gap-x-2 space-y-1 pl-0.5">
                <PureInput v-model="props.emailField.value" placeholder="Input your email" type="email"
                    @input="(emailField.status = false, emailField.description = '')" />
                <div class="flex items-center h-full">
                    <Button v-if="(typeof emailField.status != 'string')" @click="onCheckEmail" :key="emailField.value"
                        label="Check" :style="validateEmail(form.email) ? `secondary` : 'disabled'" />
                </div>
            </div>
            <div class="">
                <p v-if="!validateEmail(props.emailField.value)" class="text-xs italic text-red-500 mb-0">*Not a valid
                    email.</p>
                <!-- <p v-if="validateEmail(email)" class="text-xs italic text-gray-500">{{ emailField.description }}</p> -->
                <div v-if="emailField.status == 'error'"
                    class="mt-1 text-xxs font-thin leading-none text-gray-500 italic mb-1">Looks like you don't have the
                    account yet. Let's register!</div>
                <div v-else class="mt-1 text-xxs font-thin leading-none text-gray-500 italic mb-1">Your account is
                    exist.</div>
            </div>
        </div>

        <!-- Section: Password and Submit -->
        <div class="flex gap-y-8 flex-col">
            <div v-if="emailField.status == 'success'" class="">
                <h3 class="text-sm font-semibold leading-3 text-gray-500">Enter your password</h3>
                <PurePassword v-model="form.password" />
            </div>
            <div v-if="emailField.status == 'error'" class="space-y-2">
                <div class="">
                    <div class="text-sm font-semibold leading-none text-gray-500">Enter your password</div>
                </div>
                <PurePassword v-model="form.password" />
                <PurePassword v-model="form.passwordRepeat" placeholder="Repeat your password" />
                <p v-if="passwordField.value != passwordField.valueRepeat" class="text-red-500 italic text-sm">*Password
                    is not match</p>
            </div>

            <div v-if="typeof emailField.status === 'string'">
                <Button @click="emailField.status === 'error' ? submitFormRegister() : submitFormLogin()"
                    :style="'primary'" :label="emailField.status == 'error' ? 'register' : 'login'" />
            </div>
        </div>
        <!-- <ValidationErrors /> -->
    </div>
</template>