<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import Password from '@/Components/Auth/LoginPassword.vue';
import ValidationErrors from '@/Components/ValidationErrors.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password'),
    });
};

</script>


<template>
    <Head title="Register" />
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <Link :href="route('welcome')">
            <img class="mx-auto h-12 w-auto" src="art/new-logo-name.png" alt="Aiku" />
            </Link>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Create a new account</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Existing user?
                {{ ' ' }}
                <Link :href="route('login')" class="font-medium text-indigo-600 hover:text-indigo-500"> log in </Link>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <ValidationErrors class="mb-4" />
                <form class="space-y-6" @submit.prevent="submit">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700"> Name </label>
                        <div class="mt-1">
                            <input  v-model="form.name" id="name" name="name" type="name" autocomplete="name" required="" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700"> Email address </label>
                        <div class="mt-1">
                            <input  v-model="form.email" id="email" name="email" type="email" autocomplete="email" required="" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <Password  id="password" name="password" v-model="form.password" showPassword="true"  />
                        </div>
                    </div>



                    <div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Register</button>
                    </div>
                </form>


            </div>
        </div>
    </div>
</template>
