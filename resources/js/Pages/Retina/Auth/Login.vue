<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import LoginPassword from '@/Components/Auth/LoginPassword.vue'
import Checkbox from '@/Components/Checkbox.vue'
import ValidationErrors from '@/Components/ValidationErrors.vue'
import { trans } from 'laravel-vue-i18n'
import { onMounted, ref, nextTick } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Layout from '@/Layouts/RetinaAuth.vue'

defineOptions({ layout: Layout })
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
    inputUsername.value.focus()
})


</script>

<template>
    <Head title="Login" />
    <h1 class="text-center text-2xl font-bold text-slate-800">Login</h1>
    <form class="space-y-6 mt-7">
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700">{{ trans('Username') }}</label>
            <div class="mt-1">
                <input v-model="form.username" ref="inputUsername" id="username" name="username" :autofocus="true"
                    autocomplete="username" required=""
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    @keydown.enter="submit"
                />
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700"> {{ trans('Password') }} </label>
            <div class="mt-1 flex flex-col rounded-md shadow-sm">
                <LoginPassword :showProcessing="false" id="password" name="password" :form="form" fieldName="password" @keydown.enter="submit" />
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <Checkbox name="remember-me" id="remember-me" v-model:checked="form.remember" />
                <label for="remember-me" class="ml-2 block text-sm text-gray-900"> {{ trans('Remember me') }} </label>
            </div>
        </div>

        <div class="space-y-2">
            <Button full @click.prevent="submit" :loading="isLoading" label="Sign in"> </Button>
            <p class="text-gray-600">Don't have account yet? <Link as="span" :href="route('retina.register')" class="cursor-pointer font-bold hover:underline">Sign up</Link></p>
        </div>
    </form>

    <ValidationErrors />
</template>
