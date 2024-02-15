<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import Password from '@/Components/Auth/LoginPassword.vue';
import Checkbox from '@/Components/Checkbox.vue';
import ValidationErrors from '@/Components/ValidationErrors.vue';
import {trans} from 'laravel-vue-i18n';
import { onMounted, ref, nextTick } from 'vue'

const form = useForm({
                         username: '',
                         password: '',
                         remember: false,
                     });

const submit = () => {
    form.post(route('retina.login.store'), {
        onFinish: () => form.reset('password'),
    });
};

const inputUsername = ref(null)

onMounted(async () => {
    await nextTick()
    inputUsername.value.focus()
})


</script>

<template layout="RetinaAuthLayout">
    <Head title="Login"/>
    <form class="space-y-6" @submit.prevent="submit">
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700">{{ trans('Username') }}</label>
            <div class="mt-1">
                <input v-model="form.username" ref="inputUsername" id="username" name="username" :autofocus="true" autocomplete="username" required=""
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"/>
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700"> {{ trans('Password') }} </label>
            <div class="mt-1 flex rounded-md shadow-sm">
                <Password id="password" name="password" v-model="form.password"/>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <Checkbox name="remember-me" id="remember-me" v-model:checked="form.remember"/>
                <label for="remember-me" class="ml-2 block text-sm text-gray-900"> {{ trans('Remember me') }} </label>
            </div>


        </div>

        <div>
            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ trans('Sign in') }}
            </button>
        </div>
    </form>

    <ValidationErrors/>

</template>
