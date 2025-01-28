<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import LoginPassword from '@/Components/Auth/LoginPassword.vue'
import Checkbox from '@/Components/Checkbox.vue'
import ValidationErrors from '@/Components/ValidationErrors.vue'
import { trans } from 'laravel-vue-i18n'
import { onMounted, ref, nextTick } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import LayoutRetinaAuth from '@/Layouts/RetinaShowIris.vue'
import { usePage } from '@inertiajs/vue3'
import Image from '@/Components/Image.vue'
import PureInput from '@/Components/Pure/PureInput.vue'

defineOptions({ layout: LayoutRetinaAuth })
const form = useForm({
    username: '',
    password: '',
    remember: false,
})

const isLoading = ref(false)

const submit = () => {
    isLoading.value = true
    form.post(route('retina.login.store'), {
        onError: () => isLoading.value = false,
        onFinish: () => form.reset('password'),
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
    <!--    <h1 class="text-center text-2xl font-bold text-slate-800">Login</h1>
    
    <form class="space-y-6 mt-7">
        <div class="">
            <label for="login" class="block text-sm font-medium text-gray-700">{{ trans('Username') }}</label>
            <div class="mt-1">
                <input v-model="form.username" ref="inputUsername" id="username" name="username" :autofocus="true"
                    autocomplete="username" required
                    placeholder="username"
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    @keydown.enter="submit"
                />
            </div>
        </div>

        <div class="">
            <label for="password" class="block text-sm font-medium text-gray-700"> {{ trans('Password') }} </label>
            <div class="mt-1 flex flex-col rounded-md">
                <LoginPassword :showProcessing="false" id="password" name="password" :form="form" fieldName="password" @keydown.enter="submit" placeholder="********"/>
                <Link :href="route('retina.reset-password.edit')  " class="text-sm mt-3 hover:underline cursor-pointer">Forgot password?</Link>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <Checkbox name="remember-me" id="remember-me" v-model:checked="form.remember" />
                <label for="remember-me" class="ml-2 block text-sm select-none"> {{ trans('Remember me') }} </label>
            </div>
        </div>

        <div class="space-y-2">
            <Button full @click.prevent="submit" :loading="isLoading" label="Sign in" />
        </div>
    </form> -->


    <div class="flex items-center justify-center bg-gray-50 px-6 py-12 lg:px-8">
    <div class="w-full max-w-sm bg-transparent">
        <div class="text-center mb-6">
            <div v-if="!usePage().props?.iris?.website?.logo" class="flex items-center justify-center gap-x-2">
                <img class="h-12 w-auto" src="/art/logo-yellow.svg"
                    :alt="usePage().props.iris?.website?.name || 'App'" />
                <span style="font-family: Fira" class="text-4xl text-white leading-none">{{
                    usePage().props.iris?.website?.name }}</span>
            </div>
            <div v-else class="flex items-center justify-center gap-x-2">
                <Image class="h-12 w-auto" :src="usePage().props?.iris?.website?.logo"
                    :alt="usePage().props?.iris?.name || 'App'" />
            </div>
        </div>

        <form class="space-y-6">
            <!-- Username Field -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">
                    {{ trans('Username') }}
                </label>
                <div class="mt-1">
                    <PureInput v-model="form.username" ref="inputUsername" id="username" name="username"
                        :autofocus="true" autocomplete="username" required placeholder="username"
                        @keydown.enter="submit" />
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    {{ trans('Password') }}
                </label>
                <div class="mt-1">
                    <LoginPassword :showProcessing="false" id="password" name="password" :form="form"
                        fieldName="password" @keydown.enter="submit" placeholder="********" />
                    <div class="flex justify-end mt-2">
                        <Link :href="route('retina.reset-password.edit')"
                            class="text-sm   font-medium hover:underline transition duration-150 ease-in-out">
                        Forgot password?
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="space-y-2">
                <Button full @click.prevent="submit" :loading="isLoading" label="Sign in" type="primary"/>
            </div>

            <!-- Registration Link -->
            <div class="flex justify-center items-center mt-4">
                <p class="text-sm text-gray-500">
                    Don't have an account?
                    <Link :href="route('retina.register')"
                        class="  font-medium hover:underline transition duration-150 ease-in-out ml-1">
                    Register here
                    </Link>
                </p>
            </div>
        </form>
        <ValidationErrors />
    </div>
</div>
</template>
