<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Fri, 12 Aug 2022 15:43:19 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Inikoo
  -  Version 4.0
  -->

<script setup>

import {useForm} from '@inertiajs/vue3';
import ValidationErrors from '@/Components/ValidationErrors.vue';

const formUsername = useForm({
    username: '',
});
const submitUsername = () => {
    formUsername.post(route('setup.username'), {

    });
};


</script>

<template >


    <div  class="max-w-lg mx-auto mt-8">
        <div>
            <div class="text-center">
                <span class="mx-auto text-xl	">
                    👋 {{$page.props.auth.user.name}}
                </span>

                <h2 class="mt-2 text-lg font-medium text-gray-900">Nice name, but we need a <i>username</i></h2>
                <p class="mt-1 text-sm text-gray-500">Will be unique, useful for communications with your coworkers, use it for login, it will identify you in logs and reports, etc.</p>
            </div>
            <form @submit.prevent="submitUsername" class="mt-6 flex">
                <label for="username" class="sr-only">username</label>
                <input v-model="formUsername.username" type="text" name="username" id="username" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Your username" />
                <button type="submit" class="ml-4 flex-shrink-0 px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Continue</button>
            </form>
            <ValidationErrors class="mt-4" />
        </div>

    </div>



</template>

