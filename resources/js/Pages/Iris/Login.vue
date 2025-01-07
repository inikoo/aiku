<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import LoginPassword from '@/Components/Auth/LoginPassword.vue'
import Checkbox from '@/Components/Checkbox.vue'
import ValidationErrors from '@/Components/ValidationErrors.vue'
import { useLayoutStore } from '@/Stores/layout'
import { getIrisComponent } from '@/Composables/getIrisComponents'

import { onMounted, ref, nextTick } from 'vue'

const props = defineProps<{
  data: any,
  header : any,
  blocks: any,
}>()

// defineOptions({ layout: LayoutRetinaAuth })
const form = useForm({
    username: '',
    password: '',
    remember: false,
})

const isLoading = ref(false)

const submit = () => {
    isLoading.value = true
    form.post(route('iris.login.store'), {
        onError: (e) => {
            console.log('plm',e)
            isLoading.value = false
        },
        onFinish: () => {
            console.log('dfdfdf')
        },
        onSuccess: () => {
            form.reset('password')
            console.log('length', useLayoutStore())
            router.get(route('iris.home'))
        }
    })
}

const inputUsername = ref(null)


onMounted(async () => {
    await nextTick()
    inputUsername.value?.focus()
})


</script>

<template>
    <Head title="Login" />
    <div class="max-w-xl mx-auto flex min-h-full flex-1 flex-col justify-center py-12">
        <div class="sm:mx-auto sm:w-full">
            <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight">
                Sign in to your account
            </h2>
        </div>

        <div
            class="mt-10 sm:mx-auto sm:w-full bg-white px-6 py-12 border border-gray-100 shadow sm:rounded-lg sm:px-12">
            <form class="space-y-6" action="#" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium leading-6">
                        Email address
                    </label>
                    <div class="mt-2">
                        <input v-model="form.username" ref="inputUsername" id="username" name="username"
                            :autofocus="true" autocomplete="username" required placeholder="johndoe"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            @keydown.enter="submit" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium leading-6">Password</label>
                    <div class="mt-2">
                        <LoginPassword :showProcessing="false" id="password" name="password" :form="form" fieldName="password" @keydown.enter="submit" placeholder="********"/>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <Checkbox name="remember-me" id="remember-me" v-model:checked="form.remember" />
                        <label for="remember-me" class="select-none ml-3 block text-sm leading-6">Remember me</label>
                    </div>

                    <div class="text-sm leading-6">
                        <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot
                            password?</a>
                    </div>
                </div>

                <div>
                    <button type="submit" @click.prevent="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign
                        in</button>
                </div>
            </form>

            <div>
                <div class="relative mt-10">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-200" />
                    </div>
                    <div class="relative flex justify-center text-sm leading-6">
                        <span class="bg-white text-gray-500 px-6">Don't have account?</span>
                    </div>
                </div>

                <div class="mt-2 gap-4">
                    <!-- <a href="#"
                            class="flex w-full items-center justify-center gap-3 rounded-md bg-white px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">
                            <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24">
                                <path
                                    d="M12.0003 4.75C13.7703 4.75 15.3553 5.36002 16.6053 6.54998L20.0303 3.125C17.9502 1.19 15.2353 0 12.0003 0C7.31028 0 3.25527 2.69 1.28027 6.60998L5.27028 9.70498C6.21525 6.86002 8.87028 4.75 12.0003 4.75Z"
                                    fill="#EA4335" />
                                <path
                                    d="M23.49 12.275C23.49 11.49 23.415 10.73 23.3 10H12V14.51H18.47C18.18 15.99 17.34 17.25 16.08 18.1L19.945 21.1C22.2 19.01 23.49 15.92 23.49 12.275Z"
                                    fill="#4285F4" />
                                <path
                                    d="M5.26498 14.2949C5.02498 13.5699 4.88501 12.7999 4.88501 11.9999C4.88501 11.1999 5.01998 10.4299 5.26498 9.7049L1.275 6.60986C0.46 8.22986 0 10.0599 0 11.9999C0 13.9399 0.46 15.7699 1.28 17.3899L5.26498 14.2949Z"
                                    fill="#FBBC05" />
                                <path
                                    d="M12.0004 24.0001C15.2404 24.0001 17.9654 22.935 19.9454 21.095L16.0804 18.095C15.0054 18.82 13.6204 19.245 12.0004 19.245C8.8704 19.245 6.21537 17.135 5.2654 14.29L1.27539 17.385C3.25539 21.31 7.3104 24.0001 12.0004 24.0001Z"
                                    fill="#34A853" />
                            </svg>
                            <span class="text-sm font-semibold leading-6">Google</span>
                        </a> -->

                    <Link href="/register"
                        class="flex w-full items-center justify-center gap-3 rounded-md bg-white text-gray-600 px-3 py-2 text-sm font-semibold shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:ring-transparent">
                    Register
                    </Link>
                </div>
            </div>
        </div>

        <!-- <p class="mt-10 text-center text-sm text-gray-500">
                Not a member?
                {{ ' ' }}
                <a href="#" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Start a 14 day
                    free trial</a>
            </p> -->
    </div>

    <ValidationErrors />
</template>
