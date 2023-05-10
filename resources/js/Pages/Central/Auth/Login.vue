<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import Password from '@/Components/Auth/LoginPassword.vue';
import Checkbox from '@/Components/Checkbox.vue';

const form = useForm({
                         login   : '',
                         password: '',
                         remember: false,
                     });

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>


<template>
    <Head title="Login"/>

    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <Link :href="route('welcome')">
                <img class="mx-auto h-12 w-auto" src="art/new-logo-name.png" alt="Aiku"/>
            </Link>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Log in to your account</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                New user?
                {{ ' ' }}
                <Link :href="route('register')" class="font-medium text-indigo-600 hover:text-indigo-500"> register</Link>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">

                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700"> Email address or username</label>
                        <div class="mt-1">
                            <input v-model="form.login" id="login" name="login" autocomplete="login" required=""
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"/>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700"> Password </label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <Password id="password" name="password" v-model="form.password"/>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <Checkbox name="remember-me" id="remember-me" v-model:checked="form.remember"/>
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900"> Remember me </label>
                        </div>

                        <div class="text-sm">
                            <Link :href="route('password.request')" class="font-medium text-indigo-600 hover:text-indigo-500"> Forgot your password?</Link>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Sign in
                        </button>
                    </div>
                </form>


            </div>
        </div>
    </div>
</template>
